<?php

namespace Tests\Feature\DataForge;

use App\DataCore\Models\Company;
use App\DataCore\Models\Invoice;
use App\DataCore\Models\RawIngestion;
use App\DataForge\Services\NormalizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes do NormalizationService (DataForge).
 *
 * Cobre:
 * - Normalização de payload XML NF-e (canal xml_upload)
 * - Normalização de payload JSON genérico (canal api_push)
 * - Exceção com payload XML inválido
 * - Exceção com payload JSON inválido
 * - Persistência de invoice e invoice_items após normalização bem-sucedida
 */
class NormalizationTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private NormalizationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Normalização',
            'cnpj'      => '55666777000144',
            'slug'      => 'empresa-normalizacao',
            'plan'      => 'starter',
            'is_active' => true,
        ]);

        $this->service = app(NormalizationService::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeIngestion(array $overrides = []): RawIngestion
    {
        return RawIngestion::create(array_merge([
            'company_id'   => $this->company->id,
            'channel'      => 'api_push',
            'source'       => 'oracle_erp',
            'status'       => 'pending',
            'payload'      => '{}',
            'payload_hash' => hash('sha256', uniqid()),
            'attempts'     => 0,
        ], $overrides));
    }

    private function validJsonPayload(string $invoiceNumber = 'NF-NORM-001'): string
    {
        return json_encode([
            'source'  => 'oracle_erp',
            'invoice' => [
                'invoice_number' => $invoiceNumber,
                'issue_date'     => '2026-02-15',
                'supplier'       => [
                    'cnpj'  => '98765432000155',
                    'name'  => 'Fornecedor Normalização SA',
                    'state' => 'PR',
                ],
                'items' => [
                    [
                        'description' => 'MDF 18mm Branco',
                        'quantity'    => 100,
                        'unit_price'  => 45.50,
                        'category'    => 'MDF',
                    ],
                ],
                'totals' => [
                    'goods'   => 4550.00,
                    'freight' => 200.00,
                    'tax'     => 386.75,
                    'total'   => 5136.75,
                ],
            ],
        ]);
    }

    private function validXmlPayload(string $invoiceNumber = '12345'): string
    {
        return <<<XML
<nfeProc>
  <NFe>
    <infNFe>
      <ide>
        <mod>55</mod>
        <nNF>{$invoiceNumber}</nNF>
        <serie>1</serie>
        <dhEmi>2026-02-15T10:00:00-03:00</dhEmi>
      </ide>
      <emit>
        <CNPJ>12345678000190</CNPJ>
        <xNome>Fornecedor XML SA</xNome>
        <enderEmit>
          <UF>PR</UF>
        </enderEmit>
      </emit>
      <det>
        <prod>
          <xProd>MDF 18mm Branco</xProd>
          <qCom>100</qCom>
          <vUnCom>45.50</vUnCom>
          <vProd>4550.00</vProd>
        </prod>
      </det>
      <transp/>
      <total>
        <ICMSTot>
          <vNF>4550.00</vNF>
          <vFrete>200.00</vFrete>
          <vICMS>350.00</vICMS>
          <vIPI>36.75</vIPI>
        </ICMSTot>
      </total>
    </infNFe>
  </NFe>
</nfeProc>
XML;
    }

    // ── Normalização JSON ─────────────────────────────────────────────────────

    public function test_normalization_service_normalizes_valid_json_payload(): void
    {
        $payload   = $this->validJsonPayload('NF-JSON-001');
        $ingestion = $this->makeIngestion([
            'channel'      => 'api_push',
            'source'       => 'oracle_erp',
            'payload'      => $payload,
            'payload_hash' => hash('sha256', $payload),
        ]);

        $result = $this->service->process($ingestion);

        $this->assertArrayHasKey('invoice', $result);
        $this->assertArrayHasKey('items_count', $result);
        $this->assertEquals('NF-JSON-001', $result['invoice']->invoice_number);
    }

    public function test_normalization_persists_invoice_after_json_normalization(): void
    {
        $payload   = $this->validJsonPayload('NF-JSON-PERSIST-001');
        $ingestion = $this->makeIngestion([
            'channel'      => 'api_push',
            'source'       => 'oracle_erp',
            'payload'      => $payload,
            'payload_hash' => hash('sha256', $payload),
        ]);

        $this->service->process($ingestion);

        $this->assertDatabaseHas('invoices', [
            'company_id'     => $this->company->id,
            'invoice_number' => 'NF-JSON-PERSIST-001',
        ]);
    }

    public function test_normalization_persists_invoice_items_after_json_normalization(): void
    {
        $payload   = $this->validJsonPayload('NF-JSON-ITEMS-001');
        $ingestion = $this->makeIngestion([
            'channel'      => 'api_push',
            'source'       => 'oracle_erp',
            'payload'      => $payload,
            'payload_hash' => hash('sha256', $payload),
        ]);

        $result = $this->service->process($ingestion);

        $this->assertGreaterThan(0, $result['items_count']);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $result['invoice']->id,
            'category'   => 'MDF',
        ]);
    }

    // ── Normalização XML ──────────────────────────────────────────────────────

    public function test_normalization_service_normalizes_valid_xml_payload(): void
    {
        $xml       = $this->validXmlPayload('99999');
        $ingestion = $this->makeIngestion([
            'channel'      => 'xml_upload',
            'source'       => 'nfe_xml',
            'payload'      => $xml,
            'payload_hash' => hash('sha256', $xml),
        ]);

        $result = $this->service->process($ingestion);

        $this->assertArrayHasKey('invoice', $result);
        $this->assertDatabaseHas('invoices', [
            'company_id' => $this->company->id,
        ]);
    }

    public function test_normalization_persists_invoice_items_after_xml_normalization(): void
    {
        $xml       = $this->validXmlPayload('88888');
        $ingestion = $this->makeIngestion([
            'channel'      => 'xml_upload',
            'source'       => 'nfe_xml',
            'payload'      => $xml,
            'payload_hash' => hash('sha256', $xml),
        ]);

        $result = $this->service->process($ingestion);

        $this->assertGreaterThan(0, $result['items_count']);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $result['invoice']->id,
            'category'   => 'MDF',
        ]);
    }

    // ── Payloads inválidos ────────────────────────────────────────────────────

    public function test_normalization_throws_exception_with_invalid_json_payload(): void
    {
        $this->expectException(\Exception::class);

        $ingestion = $this->makeIngestion([
            'channel'      => 'api_push',
            'source'       => 'oracle_erp',
            'payload'      => 'NOT_VALID_JSON',
            'payload_hash' => hash('sha256', 'NOT_VALID_JSON'),
        ]);

        $this->service->process($ingestion);
    }

    public function test_normalization_throws_exception_with_invalid_xml_payload(): void
    {
        $this->expectException(\Exception::class);

        $ingestion = $this->makeIngestion([
            'channel'      => 'xml_upload',
            'source'       => 'nfe_xml',
            'payload'      => '<invalid>xml without nfe structure</invalid>',
            'payload_hash' => hash('sha256', 'invalid_xml'),
        ]);

        $this->service->process($ingestion);
    }

    public function test_normalization_throws_exception_for_unknown_channel(): void
    {
        $this->expectException(\Exception::class);

        $ingestion = $this->makeIngestion([
            'channel'      => 'canal_desconhecido',
            'source'       => 'unknown',
            'payload'      => '{}',
            'payload_hash' => hash('sha256', uniqid()),
        ]);

        $this->service->process($ingestion);
    }

    // ── Duplicidade ───────────────────────────────────────────────────────────

    public function test_normalization_throws_exception_for_duplicate_invoice(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/já importada/');

        $payload   = $this->validJsonPayload('NF-DUP-001');
        $payloadHash = hash('sha256', $payload);

        // Primeira normalização → sucesso
        $ingestion1 = $this->makeIngestion([
            'channel'      => 'api_push',
            'source'       => 'oracle_erp',
            'payload'      => $payload,
            'payload_hash' => $payloadHash,
        ]);
        $this->service->process($ingestion1);

        // Segunda com mesmo invoice_number → exceção de duplicidade
        $ingestion2 = $this->makeIngestion([
            'channel'      => 'api_push',
            'source'       => 'oracle_erp',
            'payload'      => $payload,
            'payload_hash' => hash('sha256', uniqid()), // hash diferente, mas invoice_number igual
        ]);
        $this->service->process($ingestion2);
    }
}
