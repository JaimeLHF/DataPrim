<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataCore\Requests\ImportJsonRequest;
use App\DataCore\Requests\NfeImportRequest;
use App\DataBridge\Jobs\ProcessIngestionJob;
use App\DataCore\Models\RawIngestion;
use App\DataLumen\Services\NfeXmlImportService;
use Illuminate\Http\JsonResponse;

/**
 * @group Importação de Notas Fiscais
 *
 * Endpoints para importar NF-e via XML ou JSON canônico.
 * As importações são assíncronas — retornam 202 e o processamento ocorre em fila.
 */
class InvoiceController extends Controller
{
    public function __construct(
        private NfeXmlImportService $importService
    ) {}

    /**
     * Preview XML
     *
     * Extrai e retorna dados do XML NF-e para visualização, sem persistir.
     * Este endpoint é síncrono.
     *
     * @bodyParam xml_file file required Arquivo XML NF-e modelo 55. Max 10MB.
     *
     * @response 200 {"invoice_number":"000123","issue_date":"15/01/2026","supplier":"Fornecedor Exemplo","supplier_cnpj":"12345678000190","supplier_region":"Sul","supplier_state":"PR","total_value":5136.75,"freight_value":200.0,"tax_value":386.75,"items_count":2,"items":[{"product_description":"MDF 18mm Branco","category":"MDF","quantity":100,"unit_price":45.5,"total_price":4550.0}]}
     */
    public function previewXml(NfeImportRequest $request): JsonResponse
    {
        $companyId = app('current_company_id');

        $dto = $this->importService->preview(
            $request->file('xml_file'),
            $companyId
        );

        return response()->json([
            'invoice_number'  => $dto->invoiceNumber,
            'issue_date'      => date('d/m/Y', strtotime($dto->issueDate)),
            'supplier'        => $dto->supplierName,
            'supplier_cnpj'   => $dto->supplierCnpj,
            'supplier_region' => $dto->supplierRegion,
            'supplier_state'  => $dto->supplierState,
            'total_value'     => $dto->totalValue,
            'freight_value'   => $dto->freightValue,
            'tax_value'       => $dto->taxValue,
            'items_count'     => count($dto->items),
            'items'           => $dto->items,
        ]);
    }

    /**
     * Importar XML
     *
     * Recebe arquivo XML NF-e, salva na staging area (`raw_ingestions`) e enfileira processamento assíncrono.
     * Duplicatas (mesmo hash SHA-256) retornam 409.
     *
     * @bodyParam xml_file file required Arquivo XML NF-e modelo 55. Max 10MB.
     *
     * @response 202 {"message":"XML recebido. Processamento iniciado.","ingestion_id":42,"status":"pending"}
     * @response 409 {"message":"Este arquivo já foi recebido anteriormente.","ingestion_id":42,"status":"done"}
     */
    public function importXml(NfeImportRequest $request): JsonResponse
    {
        $companyId = app('current_company_id');
        $content   = file_get_contents($request->file('xml_file')->getPathname());
        $hash      = hash('sha256', $content);

        $existing = RawIngestion::where('payload_hash', $hash)
            ->where('company_id', $companyId)
            ->first();

        if ($existing) {
            return response()->json([
                'message'      => 'Este arquivo já foi recebido anteriormente.',
                'ingestion_id' => $existing->id,
                'status'       => $existing->status,
            ], 409);
        }

        $ingestion = RawIngestion::create([
            'company_id'   => $companyId,
            'channel'      => 'xml_upload',
            'source'       => 'nfe_xml',
            'status'       => 'pending',
            'payload'      => $content,
            'payload_hash' => $hash,
        ]);

        ProcessIngestionJob::dispatch($ingestion);

        return response()->json([
            'message'      => 'XML recebido. Processamento iniciado.',
            'ingestion_id' => $ingestion->id,
            'status'       => 'pending',
        ], 202);
    }

    /**
     * Importar JSON (API Push)
     *
     * Recebe notas fiscais no formato JSON canônico. Suporta batch (múltiplas notas por request).
     * Cada nota é salva individualmente na staging area e processada de forma assíncrona.
     * Duplicatas são detectadas por hash SHA-256 e retornadas com `skipped: true`.
     *
     * @bodyParam source string required Identificador do sistema de origem. Example: oracle_erp
     * @bodyParam invoices object[] required Lista de notas fiscais.
     * @bodyParam invoices[].invoice_number string required Número da nota. Example: NF-001
     * @bodyParam invoices[].issue_date string required Data de emissão (YYYY-MM-DD). Example: 2026-01-15
     * @bodyParam invoices[].delivery_date string Data de entrega (YYYY-MM-DD). Example: 2026-01-20
     * @bodyParam invoices[].payment_terms integer Prazo de pagamento em dias. Example: 30
     * @bodyParam invoices[].supplier object required Dados do fornecedor.
     * @bodyParam invoices[].supplier.cnpj string required CNPJ do fornecedor. Example: 12345678000190
     * @bodyParam invoices[].supplier.name string required Nome do fornecedor. Example: Fornecedor Exemplo
     * @bodyParam invoices[].supplier.state string UF (2 letras). Example: PR
     * @bodyParam invoices[].items object[] required Itens da nota.
     * @bodyParam invoices[].items[].description string required Descrição do produto. Example: MDF 18mm Branco
     * @bodyParam invoices[].items[].quantity number required Quantidade. Example: 100
     * @bodyParam invoices[].items[].unit_price number required Preço unitário. Example: 45.50
     * @bodyParam invoices[].items[].category string Categoria do produto. Example: MDF
     * @bodyParam invoices[].totals object required Totais da nota.
     * @bodyParam invoices[].totals.total number required Valor total. Example: 5136.75
     * @bodyParam invoices[].totals.goods number Valor dos produtos. Example: 4550.00
     * @bodyParam invoices[].totals.freight number Valor do frete. Example: 200.00
     * @bodyParam invoices[].totals.tax number Valor dos impostos. Example: 386.75
     *
     * @response 202 {"message":"1 nota(s) recebida(s). Processamento iniciado.","ingestions":[{"invoice_number":"NF-001","ingestion_id":43,"status":"pending","skipped":false}]}
     */
    public function importJson(ImportJsonRequest $request): JsonResponse
    {
        $companyId = app('current_company_id');
        $source    = $request->input('source');
        $invoices  = $request->input('invoices');
        $results   = [];

        foreach ($invoices as $invoice) {
            $payload = json_encode(['source' => $source, 'invoice' => $invoice]);
            $hash    = hash('sha256', $payload);

            $existing = RawIngestion::where('payload_hash', $hash)
                ->where('company_id', $companyId)
                ->first();

            if ($existing) {
                $results[] = [
                    'invoice_number' => $invoice['invoice_number'],
                    'ingestion_id'   => $existing->id,
                    'status'         => $existing->status,
                    'skipped'        => true,
                    'message'        => 'Nota já recebida anteriormente.',
                ];
                continue;
            }

            $ingestion = RawIngestion::create([
                'company_id'   => $companyId,
                'channel'      => 'api_push',
                'source'       => $source,
                'status'       => 'pending',
                'payload'      => $payload,
                'payload_hash' => $hash,
            ]);

            ProcessIngestionJob::dispatch($ingestion);

            $results[] = [
                'invoice_number' => $invoice['invoice_number'],
                'ingestion_id'   => $ingestion->id,
                'status'         => 'pending',
                'skipped'        => false,
            ];
        }

        return response()->json([
            'message'  => count($results) . ' nota(s) recebida(s). Processamento iniciado.',
            'ingestions' => $results,
        ], 202);
    }
}
