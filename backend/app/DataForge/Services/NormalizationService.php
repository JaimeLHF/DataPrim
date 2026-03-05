<?php

namespace App\DataForge\Services;

use App\DataBridge\Adapters\BlingAdapter;
use App\DataBridge\Adapters\GenericJsonAdapter;
use App\DataBridge\Adapters\NfeXmlAdapter;
use App\DataBridge\Adapters\TinyErpAdapter;
use App\DataBridge\Contracts\InvoiceAdapterInterface;
use App\DataBridge\DTOs\CanonicalInvoiceDto;
use App\DataCore\Models\Invoice;
use App\DataCore\Models\RawIngestion;
use App\DataCore\Models\Supplier;
use Exception;

class NormalizationService
{
    /** @var InvoiceAdapterInterface[] */
    private array $adapters;

    public function __construct(NfeXmlAdapter $xml, GenericJsonAdapter $json, BlingAdapter $bling, TinyErpAdapter $tiny)
    {
        $this->adapters = [$xml, $json, $bling, $tiny];
    }

    /**
     * Processa uma ingestão bruta: normaliza e persiste no modelo canônico.
     *
     * @return array{invoice: Invoice, items: array, items_count: int}
     * @throws Exception
     */
    public function process(RawIngestion $ingestion): array
    {
        $ingestion->markAsProcessing();

        $adapter = $this->resolveAdapter($ingestion->channel, $ingestion->source);
        $dto     = $adapter->normalize($ingestion->payload, $ingestion->company_id);

        // Verificar duplicidade de invoice_number dentro da empresa
        if (Invoice::where('company_id', $dto->companyId)
            ->where('invoice_number', $dto->invoiceNumber)
            ->exists()
        ) {
            throw new Exception("NF-e número {$dto->invoiceNumber} já importada anteriormente.");
        }

        $result = $this->persist($dto);

        $ingestion->markAsDone();

        return $result;
    }

    /**
     * Persiste o DTO canônico no banco de dados.
     */
    private function persist(CanonicalInvoiceDto $dto): array
    {
        $supplier = Supplier::firstOrCreate(
            [
                'cnpj'       => $dto->supplier->cnpj,
                'company_id' => $dto->companyId,
            ],
            [
                'name'   => $dto->supplier->name,
                'region' => $dto->supplier->region,
                'state'  => $dto->supplier->state,
            ]
        );

        $invoiceData = [
            'company_id'     => $dto->companyId,
            'supplier_id'    => $supplier->id,
            'invoice_number' => $dto->invoiceNumber,
            'issue_date'     => $dto->issueDate,
            'total_value'    => $dto->totals->totalValue,
            'freight_value'  => $dto->totals->freightValue,
            'tax_value'      => $dto->totals->taxValue,
        ];

        if ($dto->deliveryDate) {
            $invoiceData['delivery_date'] = $dto->deliveryDate;
        }
        if ($dto->paymentTerms) {
            $invoiceData['payment_terms'] = $dto->paymentTerms;
        }
        if ($dto->sourceSystem) {
            $invoiceData['source_system'] = $dto->sourceSystem;
        }
        if ($dto->sourceId) {
            $invoiceData['source_id'] = $dto->sourceId;
        }

        $invoice = Invoice::create($invoiceData);

        $items = [];
        foreach ($dto->items as $item) {
            $items[] = $invoice->items()->create([
                'product_description' => $item->description,
                'category'            => $item->category,
                'quantity'            => $item->quantity,
                'unit_price'          => $item->unitPrice,
                'total_price'         => $item->totalPrice,
            ]);
        }

        return [
            'invoice'     => $invoice->load('supplier'),
            'items'       => $items,
            'items_count' => count($items),
        ];
    }

    /**
     * Seleciona o adapter para uso externo (ex: WebhookController para validar assinatura).
     *
     * @throws Exception
     */
    public function resolveAdapterPublic(string $channel, ?string $source): InvoiceAdapterInterface
    {
        return $this->resolveAdapter($channel, $source);
    }

    /**
     * Seleciona o adapter adequado para o canal/source.
     *
     * @throws Exception
     */
    private function resolveAdapter(string $channel, ?string $source): InvoiceAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->canHandle($channel, $source)) {
                return $adapter;
            }
        }

        throw new Exception("Nenhum adapter disponível para canal '{$channel}' e source '{$source}'.");
    }
}
