<?php

namespace App\DataBridge\Adapters;

use App\DataBridge\Contracts\InvoiceAdapterInterface;
use App\DataBridge\DTOs\CanonicalInvoiceDto;
use App\DataBridge\DTOs\CanonicalItemDto;
use App\DataBridge\DTOs\CanonicalSupplierDto;
use App\DataBridge\DTOs\CanonicalTotalsDto;
use Exception;

class GenericJsonAdapter implements InvoiceAdapterInterface
{
    public function canHandle(string $channel, ?string $source): bool
    {
        return $channel === 'api_push';
    }

    /**
     * Normaliza um payload JSON no formato canônico.
     *
     * Formato esperado (uma invoice por payload):
     * {
     *   "source": "oracle_erp",
     *   "invoice": {
     *     "invoice_number": "NF-001-1",
     *     "issue_date": "2026-01-15",
     *     "delivery_date": "2026-01-20",    // opcional
     *     "payment_terms": 30,               // opcional
     *     "supplier": { "cnpj": "12345678000190", "name": "Fornecedor", "state": "SP" },
     *     "items": [{ "description": "...", "quantity": 100, "unit_price": 45.5, "category": "MDF" }],
     *     "totals": { "goods": 4550.0, "freight": 200.0, "tax": 386.75, "total": 5136.75 }
     *   }
     * }
     *
     * @throws Exception
     */
    public function normalize(string $rawPayload, int $companyId): CanonicalInvoiceDto
    {
        $data = json_decode($rawPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Payload JSON inválido: ' . json_last_error_msg());
        }

        $invoice = $data['invoice'] ?? null;
        if (!$invoice) {
            throw new Exception('Campo "invoice" não encontrado no payload.');
        }

        $source      = $data['source'] ?? 'api_push';
        $supplierRaw = $invoice['supplier'] ?? [];
        $itemsRaw    = $invoice['items'] ?? [];
        $totalsRaw   = $invoice['totals'] ?? [];

        $uf     = strtoupper($supplierRaw['state'] ?? '');
        $region = $this->mapUfToRegion($uf);

        $supplier = new CanonicalSupplierDto(
            cnpj:   $this->cleanCnpj($supplierRaw['cnpj'] ?? ''),
            name:   $supplierRaw['name'] ?? 'Fornecedor Desconhecido',
            region: $region,
            state:  $uf,
        );

        $items = [];
        foreach ($itemsRaw as $item) {
            $qty   = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $total = (float) ($item['total_price'] ?? $qty * $price);

            $items[] = new CanonicalItemDto(
                description: $item['description'] ?? 'Produto',
                category:    $item['category'] ?? 'Outros',
                quantity:    $qty,
                unitPrice:   $price,
                totalPrice:  $total,
            );
        }

        $totals = new CanonicalTotalsDto(
            goodsValue:   (float) ($totalsRaw['goods'] ?? 0),
            freightValue: (float) ($totalsRaw['freight'] ?? 0),
            taxValue:     (float) ($totalsRaw['tax'] ?? 0),
            totalValue:   (float) ($totalsRaw['total'] ?? 0),
        );

        return new CanonicalInvoiceDto(
            companyId:     $companyId,
            invoiceNumber: $invoice['invoice_number'] ?? '',
            issueDate:     $invoice['issue_date'] ?? date('Y-m-d'),
            supplier:      $supplier,
            items:         $items,
            totals:        $totals,
            sourceSystem:  $source,
            sourceId:      $invoice['source_id'] ?? null,
            deliveryDate:  $invoice['delivery_date'] ?? null,
            paymentTerms:  isset($invoice['payment_terms']) ? (int) $invoice['payment_terms'] : null,
        );
    }

    private function cleanCnpj(string $cnpj): string
    {
        return preg_replace('/\D/', '', $cnpj);
    }

    private function mapUfToRegion(string $uf): string
    {
        $map = [
            'PR' => 'Sul', 'SC' => 'Sul', 'RS' => 'Sul',
            'SP' => 'Sudeste', 'RJ' => 'Sudeste', 'MG' => 'Sudeste', 'ES' => 'Sudeste',
            'MT' => 'Centro-Oeste', 'MS' => 'Centro-Oeste', 'GO' => 'Centro-Oeste', 'DF' => 'Centro-Oeste',
            'BA' => 'Nordeste', 'SE' => 'Nordeste', 'AL' => 'Nordeste', 'PE' => 'Nordeste',
            'PB' => 'Nordeste', 'RN' => 'Nordeste', 'CE' => 'Nordeste', 'PI' => 'Nordeste', 'MA' => 'Nordeste',
            'AM' => 'Norte', 'PA' => 'Norte', 'AC' => 'Norte', 'RO' => 'Norte',
            'RR' => 'Norte', 'AP' => 'Norte', 'TO' => 'Norte',
        ];

        return $map[$uf] ?? 'Desconhecida';
    }
}
