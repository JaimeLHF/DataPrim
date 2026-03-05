<?php

namespace App\DataBridge\Adapters;

use App\DataBridge\Contracts\InvoiceAdapterInterface;
use App\DataBridge\DTOs\CanonicalInvoiceDto;
use App\DataBridge\DTOs\CanonicalItemDto;
use App\DataBridge\DTOs\CanonicalSupplierDto;
use App\DataBridge\DTOs\CanonicalTotalsDto;
use Exception;

/**
 * Adapter para dados do Tiny ERP — Canal 4 (Conector Pull Ativo).
 *
 * Normaliza o payload gerado pelo TinyErpConnector::fetchInvoicesSince()
 * para o formato canônico da plataforma.
 *
 * Payload esperado (string JSON):
 * {
 *   "event": "nota_fiscal.inserida",
 *   "source": "tinyerp",
 *   "data": {
 *     "nota_fiscal": {
 *       "id": "123",
 *       "numero": "001",
 *       "tipo": "E",
 *       "data_emissao": "15/01/2026",
 *       "contato": { "nome": "Fornecedor X", "cpf_cnpj": "12.345.678/0001-90", "uf": "PR" },
 *       "itens": [{ "descricao": "MDF 18mm", "quantidade": 100, "valor_unitario": 45.50, "valor": 4550.00 }],
 *       "valor_frete": "200.00",
 *       "valor_total_nota": "5136.75"
 *     }
 *   }
 * }
 */
class TinyErpAdapter implements InvoiceAdapterInterface
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

    // ─── InvoiceAdapterInterface ──────────────────────────────────────────

    /**
     * Este adapter processa canal 'connector' com source 'tinyerp'.
     */
    public function canHandle(string $channel, ?string $source): bool
    {
        return $channel === 'connector' && $source === 'tinyerp';
    }

    /**
     * Normaliza o rawPayload JSON para CanonicalInvoiceDto.
     */
    public function normalize(string $rawPayload, int $companyId): CanonicalInvoiceDto
    {
        $payload = json_decode($rawPayload, true);

        if (!$payload || empty($payload['data']['nota_fiscal'])) {
            throw new Exception('TinyErpAdapter: nota_fiscal ausente no payload.');
        }

        $nf = $payload['data']['nota_fiscal'];

        // ─ Fornecedor ────────────────────────────────────────────────────
        $contato = $nf['contato'] ?? [];
        $uf      = strtoupper($contato['uf'] ?? '');

        $supplier = new CanonicalSupplierDto(
            name: $contato['nome']      ?? 'Desconhecido',
            cnpj: $this->cleanDocument($contato['cpf_cnpj'] ?? ''),
            state: $uf,
            region: self::UF_TO_REGION[$uf] ?? 'Desconhecida',
        );

        // ─ Itens ─────────────────────────────────────────────────────────
        $itens = $nf['itens'] ?? [];
        $items = array_map(fn($item) => new CanonicalItemDto(
            description: $item['descricao']      ?? 'Item sem descrição',
            category: $this->inferCategory($item['descricao'] ?? ''),
            quantity: (float) ($item['quantidade']     ?? 0),
            unitPrice: (float) ($item['valor_unitario'] ?? 0),
            totalPrice: (float) ($item['valor']          ?? 0),
        ), $itens);

        // ─ Totais ────────────────────────────────────────────────────────
        $totalBruto  = (float) ($nf['valor_total_nota'] ?? 0);
        $frete       = (float) ($nf['valor_frete']      ?? 0);
        $goodsValue  = $totalBruto - $frete; // estimativa: sem impostos separados

        $totals = new CanonicalTotalsDto(
            goodsValue: $goodsValue,
            freightValue: $frete,
            taxValue: 0.0,
            totalValue: $totalBruto,
        );

        // ─ Data de emissão ───────────────────────────────────────────────
        $issueDate = $this->parseDate($nf['data_emissao'] ?? '');

        return new CanonicalInvoiceDto(
            companyId: $companyId,
            invoiceNumber: (string) ($nf['numero'] ?? $nf['id'] ?? ''),
            issueDate: $issueDate,
            supplier: $supplier,
            items: $items,
            totals: $totals,
            sourceSystem: 'tinyerp',
            sourceId: (string) ($nf['id'] ?? ''),
        );
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /** Converte dd/mm/yyyy (formato Tiny) para yyyy-mm-dd */
    private function parseDate(string $date): string
    {
        if (empty($date)) return now()->toDateString();
        $parts = explode('/', $date);
        if (count($parts) === 3) {
            return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
        }
        return $date;
    }

    /** Remove caracteres não numéricos do CNPJ/CPF */
    private function cleanDocument(string $doc): string
    {
        return preg_replace('/\D/', '', $doc) ?? '';
    }

    /**
     * Infere categoria pelo nome do produto.
     * Mesmo mapeamento do BlingAdapter para consistência.
     */
    private function inferCategory(string $description): string
    {
        $desc = mb_strtolower($description);

        if (str_contains($desc, 'mdf') || str_contains($desc, 'madeira') || str_contains($desc, 'compensado')) {
            return 'MDF';
        }
        if (str_contains($desc, 'ferrag') || str_contains($desc, 'dobradica') || str_contains($desc, 'parafuso')) {
            return 'Ferragens';
        }
        if (str_contains($desc, 'cola') || str_contains($desc, 'quimic') || str_contains($desc, 'tinta') || str_contains($desc, 'verniz')) {
            return 'Químicos';
        }
        if (str_contains($desc, 'arame') || str_contains($desc, 'grampo') || str_contains($desc, 'prego')) {
            return 'Aramados';
        }
        if (str_contains($desc, 'embala') || str_contains($desc, 'caixa') || str_contains($desc, 'papelão')) {
            return 'Embalagens';
        }

        return 'Acessórios';
    }
}
