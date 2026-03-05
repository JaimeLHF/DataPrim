<?php

namespace App\DataBridge\Adapters;

use App\DataBridge\Contracts\WebhookAdapterInterface;
use App\DataBridge\DTOs\CanonicalInvoiceDto;
use App\DataBridge\DTOs\CanonicalItemDto;
use App\DataBridge\DTOs\CanonicalSupplierDto;
use App\DataBridge\DTOs\CanonicalTotalsDto;
use Exception;

/**
 * Adapter para webhooks do Bling ERP.
 *
 * Documentação oficial: https://developer.bling.com.br/webhooks
 * Assinatura: header X-Bling-Signature-256 com formato "sha256=<hmac-hex>"
 */
class BlingAdapter implements WebhookAdapterInterface
{
    private const UF_TO_REGION = [
        'PR' => 'Sul',
        'SC' => 'Sul',
        'RS' => 'Sul',
        'SP' => 'Sudeste',
        'RJ' => 'Sudeste',
        'MG' => 'Sudeste',
        'ES' => 'Sudeste',
        'MT' => 'Centro-Oeste',
        'MS' => 'Centro-Oeste',
        'GO' => 'Centro-Oeste',
        'DF' => 'Centro-Oeste',
        'BA' => 'Nordeste',
        'SE' => 'Nordeste',
        'AL' => 'Nordeste',
        'PE' => 'Nordeste',
        'PB' => 'Nordeste',
        'RN' => 'Nordeste',
        'CE' => 'Nordeste',
        'PI' => 'Nordeste',
        'MA' => 'Nordeste',
        'AM' => 'Norte',
        'PA' => 'Norte',
        'AC' => 'Norte',
        'RO' => 'Norte',
        'RR' => 'Norte',
        'AP' => 'Norte',
        'TO' => 'Norte',
    ];

    // ─── WebhookAdapterInterface ───────────────────────────────────────────

    public function signatureHeader(): string
    {
        return 'X-Bling-Signature-256';
    }

    /**
     * Valida a assinatura HMAC SHA-256 do Bling.
     *
     * O Bling envia: X-Bling-Signature-256: sha256=<hash_hex>
     * Verificamos computando hash_hmac('sha256', $rawPayload, $secret)
     * e comparando com o hash extraído do header.
     */
    public function validateSignature(
        string $rawPayload,
        string $signatureHeader,
        string $secret
    ): bool {
        // Extrai o hash do formato "sha256=<hex>"
        if (!str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $receivedHash = substr($signatureHeader, 7); // Remove "sha256="
        $expectedHash = hash_hmac('sha256', $rawPayload, $secret);

        return hash_equals($expectedHash, $receivedHash);
    }

    // ─── InvoiceAdapterInterface ───────────────────────────────────────────

    public function canHandle(string $channel, ?string $source): bool
    {
        // Aceita webhooks E payloads vindos do BlingConnector (canal pull)
        return in_array($channel, ['webhook', 'connector']) && $source === 'bling';
    }

    /**
     * Normaliza o payload do Bling para o modelo canônico.
     *
     * Suporta o evento "invoice.created" do Bling v2.
     * Payload esperado:
     * {
     *   "eventId": "abc123",
     *   "event":   "invoice.created",
     *   "data": {
     *     "id":          12345678,
     *     "numero":      "000001",
     *     "serie":       "1",
     *     "dataEmissao": "2026-01-15 10:00:00",
     *     "contato": { "nome": "Fornecedor", "numeroDocumento": "12.345.678/0001-90" },
     *     "itens": [{ "descricao": "MDF 18mm", "quantidade": 100, "valor": 45.50, "valorTotal": 4550.00 }],
     *     "valorNota": 5136.75,
     *     "valorFrete": 200.00
     *   }
     * }
     *
     * @throws Exception
     */
    public function normalize(string $rawPayload, int $companyId): CanonicalInvoiceDto
    {
        $data = json_decode($rawPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Payload Bling inválido: ' . json_last_error_msg());
        }

        // Valida o tipo de evento — apenas invoice.created e invoice.updated
        $event = $data['event'] ?? '';
        if (!in_array($event, ['invoice.created', 'invoice.updated'])) {
            throw new Exception("Evento Bling não suportado: '{$event}'. Suportados: invoice.created, invoice.updated");
        }

        $invoice = $data['data'] ?? null;
        if (!$invoice) {
            throw new Exception('Campo "data" não encontrado no payload do Bling.');
        }

        $contato  = $invoice['contato'] ?? [];
        $endereco = $contato['endereco'] ?? [];
        $cnpj     = $this->cleanDocument($contato['numeroDocumento'] ?? '');
        $uf       = strtoupper($endereco['uf'] ?? $contato['uf'] ?? '');  // endereco.uf ou contato.uf

        $supplier = new CanonicalSupplierDto(
            cnpj: $cnpj,
            name: $contato['nome'] ?? 'Fornecedor Desconhecido',
            region: self::UF_TO_REGION[$uf] ?? 'Desconhecida',
            state: $uf,
        );

        $items = [];
        $goodsValue = 0.0;
        foreach (($invoice['itens'] ?? []) as $item) {
            $qty        = (float) ($item['quantidade'] ?? 0);
            $price      = (float) ($item['valor']     ?? 0); // preço unitário
            $itemTotal  = (float) ($item['valorTotal'] ?? ($qty * $price)); // total por item
            $goodsValue += $itemTotal;

            $items[] = new CanonicalItemDto(
                description: $item['descricao'] ?? 'Produto',
                category: (!empty($item['codigo']) ? $item['codigo'] : 'Outros'),
                quantity: $qty,
                unitPrice: $price,
                totalPrice: $itemTotal,
            );
        }

        // Bling API v3: valorNota = total da nota, valorFrete = frete
        // totalProdutos/total/totalFrete não existem no endpoint /nfe/{id}
        $totalValue   = (float) ($invoice['valorNota']  ?? 0);
        $freightValue = (float) ($invoice['valorFrete'] ?? 0);
        // Impostos não ficam disponíveis como campo raiz — usa goodsValue calculado dos itens
        $taxValue = max(0.0, $totalValue - $goodsValue - $freightValue);

        $totals = new CanonicalTotalsDto(
            goodsValue:   $goodsValue,
            freightValue: $freightValue,
            taxValue:     $taxValue,
            totalValue:   $totalValue,
        );

        // Número da nota: combina número + série
        $numero = (string) ($invoice['numero'] ?? '');
        $serie  = (string) ($invoice['serie']  ?? '1');
        $invoiceNumber = "BLING-{$numero}-{$serie}";

        // Data de emissão
        $dataEmissao = $invoice['dataEmissao'] ?? date('Y-m-d H:i:s');
        $issueDate   = date('Y-m-d', strtotime($dataEmissao));

        return new CanonicalInvoiceDto(
            companyId: $companyId,
            invoiceNumber: $invoiceNumber,
            issueDate: $issueDate,
            supplier: $supplier,
            items: $items,
            totals: $totals,
            sourceSystem: 'bling',
            sourceId: (string) ($invoice['id'] ?? null),
            deliveryDate: null,
            paymentTerms: null,
        );
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    private function cleanDocument(string $doc): string
    {
        return preg_replace('/\D/', '', $doc);
    }
}
