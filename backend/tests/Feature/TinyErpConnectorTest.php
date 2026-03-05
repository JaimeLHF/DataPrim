<?php

namespace Tests\Feature;

use App\DataBridge\Adapters\TinyErpAdapter;
use App\DataBridge\Connectors\TinyErpConnector;
use App\DataCore\Exceptions\ConnectorException;
use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\ErpConnector;
use App\DataCore\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Testes da integração TinyERP — Conector + Adapter.
 *
 * Usa Http::fake() para mockar a API do Tiny — sem chamadas reais.
 */
class TinyErpConnectorTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User    $admin;
    private string  $adminToken;

    /** Payload de NF-e simulando a resposta da API Tiny */
    private array $notaFiscalPayload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Móveis Test',
            'cnpj'      => '12345678000190',
            'slug'      => 'moveis-test',
            'plan'      => 'starter',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create();
        CompanyUser::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->admin->id,
            'role'       => 'admin',
            'joined_at'  => now(),
        ]);
        $this->adminToken = $this->admin->createToken('test')->plainTextToken;

        $this->notaFiscalPayload = [
            'retorno' => [
                'status' => 'OK',
                'notas_fiscais' => [
                    [
                        'nota_fiscal' => [
                            'id'              => '789',
                            'numero'          => '000042',
                            'serie'           => '1',
                            'tipo'            => 'E',
                            'data_emissao'    => '15/01/2026',
                            'contato'         => [
                                'nome'      => 'Fornecedor Tiny Ltda',
                                'cpf_cnpj'  => '98.765.432/0001-10',
                                'uf'        => 'SC',
                            ],
                            'itens' => [
                                [
                                    'descricao'      => 'MDF 15mm Branco',
                                    'quantidade'     => '50',
                                    'valor_unitario' => '42.00',
                                    'valor'          => '2100.00',
                                ],
                            ],
                            'valor_frete'      => '150.00',
                            'valor_total_nota' => '2490.00',
                        ],
                    ],
                ],
            ],
        ];
    }

    // ─── TinyErpConnector ─────────────────────────────────────────────────────

    /** @test */
    public function tiny_connector_yields_payloads_from_api(): void
    {
        Http::fake([
            'api.tiny.com.br/*' => Http::sequence()
                ->push($this->notaFiscalPayload, 200)
                ->push(['retorno' => ['status' => 'OK', 'notas_fiscais' => []]], 200), // fim da paginação
        ]);

        $connector = new TinyErpConnector(['api_token' => 'meu-token-teste']);
        $payloads  = iterator_to_array($connector->fetchInvoicesSince(Carbon::parse('2026-01-01')));

        $this->assertCount(1, $payloads);
        $data = json_decode($payloads[0], true);
        $this->assertEquals('nota_fiscal.inserida', $data['event']);
        $this->assertEquals('tinyerp', $data['source']);
        $this->assertEquals('000042', $data['data']['nota_fiscal']['numero']);
    }

    /** @test */
    public function tiny_connector_throws_on_invalid_token(): void
    {
        Http::fake([
            'api.tiny.com.br/*' => Http::response(['retorno' => ['status' => 'Erro', 'erros' => [['erro' => 'Token inválido']]]], 200),
        ]);

        $this->expectException(ConnectorException::class);
        $this->expectExceptionMessageMatches('/Token inválido/');

        $connector = new TinyErpConnector(['api_token' => 'token-invalido']);
        $connector->testConnection();
    }

    /** @test */
    public function tiny_connector_throws_when_token_missing(): void
    {
        $this->expectException(ConnectorException::class);
        $this->expectExceptionMessageMatches('/api_token/');

        $connector = new TinyErpConnector(['api_token' => '']);
        $connector->testConnection();
    }

    /** @test */
    public function tiny_connector_test_connection_returns_true_on_success(): void
    {
        Http::fake([
            'api.tiny.com.br/*' => Http::response(['retorno' => ['status' => 'OK', 'notas_fiscais' => []]], 200),
        ]);

        $connector = new TinyErpConnector(['api_token' => 'token-valido']);
        $result    = $connector->testConnection();

        $this->assertTrue($result);
    }

    /** @test */
    public function tiny_connector_paginates_correctly(): void
    {
        $page2 = $this->notaFiscalPayload;
        $page2['retorno']['notas_fiscais'][0]['nota_fiscal']['numero'] = '000043';

        Http::fake([
            'api.tiny.com.br/*' => Http::sequence()
                ->push($this->notaFiscalPayload, 200)
                ->push($page2, 200)
                ->push(['retorno' => ['status' => 'OK', 'notas_fiscais' => []]], 200),
        ]);

        $connector = new TinyErpConnector(['api_token' => 'token']);
        $payloads  = iterator_to_array($connector->fetchInvoicesSince(Carbon::parse('2026-01-01')));

        $this->assertCount(2, $payloads);
        $this->assertStringContainsString('000042', $payloads[0]);
        $this->assertStringContainsString('000043', $payloads[1]);
    }

    // ─── TinyErpAdapter ──────────────────────────────────────────────────────

    /** @test */
    public function tiny_adapter_can_handle_connector_source(): void
    {
        $adapter = new TinyErpAdapter();

        $this->assertTrue($adapter->canHandle('connector', 'tinyerp'));
        $this->assertFalse($adapter->canHandle('connector', 'bling'));
        $this->assertFalse($adapter->canHandle('webhook', 'tinyerp'));
    }

    /** @test */
    public function tiny_adapter_normalizes_payload_correctly(): void
    {
        $rawPayload = json_encode([
            'event'  => 'nota_fiscal.inserida',
            'source' => 'tinyerp',
            'data'   => $this->notaFiscalPayload['retorno']['notas_fiscais'][0],
        ]);

        $adapter = new TinyErpAdapter();
        $dto     = $adapter->normalize($rawPayload, $this->company->id);

        $this->assertEquals('000042',          $dto->invoiceNumber);
        $this->assertEquals('2026-01-15',      $dto->issueDate);
        $this->assertEquals($this->company->id, $dto->companyId);
        $this->assertEquals('tinyerp',         $dto->sourceSystem);
        $this->assertEquals('789',             $dto->sourceId);
    }

    /** @test */
    public function tiny_adapter_normalizes_supplier_correctly(): void
    {
        $rawPayload = json_encode([
            'event'  => 'nota_fiscal.inserida',
            'source' => 'tinyerp',
            'data'   => $this->notaFiscalPayload['retorno']['notas_fiscais'][0],
        ]);

        $adapter  = new TinyErpAdapter();
        $dto      = $adapter->normalize($rawPayload, $this->company->id);
        $supplier = $dto->supplier;

        $this->assertEquals('Fornecedor Tiny Ltda', $supplier->name);
        $this->assertEquals('98765432000110',        $supplier->cnpj); // sem pontuação
        $this->assertEquals('SC',                    $supplier->state);
        $this->assertEquals('Sul',                   $supplier->region);
    }

    /** @test */
    public function tiny_adapter_normalizes_totals_correctly(): void
    {
        $rawPayload = json_encode([
            'event'  => 'nota_fiscal.inserida',
            'source' => 'tinyerp',
            'data'   => $this->notaFiscalPayload['retorno']['notas_fiscais'][0],
        ]);

        $adapter = new TinyErpAdapter();
        $dto     = $adapter->normalize($rawPayload, $this->company->id);
        $totals  = $dto->totals;

        $this->assertEquals(2490.0, $totals->totalValue);
        $this->assertEquals(150.0,  $totals->freightValue);
        $this->assertEquals(0.0,    $totals->taxValue);
        $this->assertEquals(2340.0, $totals->goodsValue); // 2490 - 150
    }

    /** @test */
    public function tiny_adapter_infers_mdf_category(): void
    {
        $rawPayload = json_encode([
            'event'  => 'nota_fiscal.inserida',
            'source' => 'tinyerp',
            'data'   => $this->notaFiscalPayload['retorno']['notas_fiscais'][0],
        ]);

        $adapter = new TinyErpAdapter();
        $dto     = $adapter->normalize($rawPayload, $this->company->id);

        $this->assertEquals('MDF', $dto->items[0]->category);
    }

    // ─── Factory ─────────────────────────────────────────────────────────────

    /** @test */
    public function connector_factory_creates_tiny_connector(): void
    {
        $erp = ErpConnector::make([
            'company_id'     => $this->company->id,
            'erp_type'       => 'tinyerp',
            'credentials'    => encrypt(json_encode(['api_token' => 'tk-fake'])),
            'sync_frequency' => 360,
        ]);

        // Acessa via factory usando o model
        $erp->id = 99; // simula model persistido
        $connector = \App\DataBridge\Connectors\ConnectorFactory::make($erp);

        $this->assertInstanceOf(TinyErpConnector::class, $connector);
        $this->assertEquals('tinyerp', $connector->erpType());
    }
}
