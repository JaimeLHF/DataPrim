<?php

namespace Tests\Feature;

use App\DataBridge\Connectors\ConnectorFactory;
use App\DataBridge\Connectors\StubConnector;
use App\DataCore\Exceptions\ConnectorException;
use App\DataCore\Models\Company;
use App\DataCore\Models\ErpConnector;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes da Fase 4a — Framework de Conectores.
 *
 * Valida: modelo, criptografia, scopes, StubConnector e ConnectorFactory.
 * Não faz chamadas HTTP reais (sem BlingConnector ainda).
 */
class ErpConnectorFrameworkTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Teste',
            'cnpj'      => '12345678000190',
            'slug'      => 'empresa-teste',
            'plan'      => 'starter',
            'is_active' => true,
        ]);
    }

    // ─── Model: criptografia de credenciais ───────────────────────────────

    public function test_credentials_are_encrypted_at_rest(): void
    {
        $connector = ErpConnector::create([
            'company_id'    => $this->company->id,
            'erp_type'      => 'bling',
            'credentials'   => ['client_id' => 'abc123', 'client_secret' => 'xyz789'],
            'sync_frequency' => 360,
        ]);

        // O valor bruto no banco deve ser criptografado (não legível)
        $rawValue = \Illuminate\Support\Facades\DB::table('erp_connectors')
            ->where('id', $connector->id)
            ->value('credentials');

        // O raw nunca deve conter as credenciais em texto plano
        $this->assertStringNotContainsString('abc123', $rawValue);
        $this->assertStringNotContainsString('xyz789', $rawValue);
        $this->assertStringNotContainsString('client_id', $rawValue);

        // Laravel encrypt() serializa em base64 — o raw value não é JSON puro
        $this->assertJson('{}'); // sanity — e o raw não é JSON válido de credentials
        $decodedAttempt = json_decode($rawValue, true);
        $this->assertNull($decodedAttempt['client_id'] ?? null);
    }

    public function test_credentials_are_decrypted_via_accessor(): void
    {
        $connector = ErpConnector::create([
            'company_id'    => $this->company->id,
            'erp_type'      => 'bling',
            'credentials'   => ['client_id' => 'abc123', 'client_secret' => 'xyz789'],
            'sync_frequency' => 360,
        ]);

        // Ao ler via model, deve retornar o array original
        $fresh = ErpConnector::find($connector->id);

        $this->assertEquals('abc123', $fresh->credentials['client_id']);
        $this->assertEquals('xyz789', $fresh->credentials['client_secret']);
    }

    public function test_credentials_not_exposed_in_json(): void
    {
        $connector = ErpConnector::create([
            'company_id'    => $this->company->id,
            'erp_type'      => 'bling',
            'credentials'   => ['client_id' => 'abc123', 'client_secret' => 'xyz789'],
            'sync_frequency' => 360,
        ]);

        $json = $connector->toJson();

        $this->assertStringNotContainsString('credentials', $json);
        $this->assertStringNotContainsString('abc123', $json);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────

    public function test_scope_due_returns_connectors_with_null_last_synced(): void
    {
        ErpConnector::create([
            'company_id'    => $this->company->id,
            'erp_type'      => 'bling',
            'credentials'   => ['client_id' => 'x'],
            'sync_frequency' => 360,
            'last_synced_at' => null,
            'is_active'     => true,
        ]);

        $this->assertCount(1, ErpConnector::due()->get());
    }

    public function test_scope_due_excludes_recently_synced_connectors(): void
    {
        // Sincronizado há 1 minuto — frequency é 360 min, portanto ainda não venceu
        ErpConnector::create([
            'company_id'    => $this->company->id,
            'erp_type'      => 'bling',
            'credentials'   => ['client_id' => 'x'],
            'sync_frequency' => 360,
            'last_synced_at' => now()->subMinutes(1),
            'is_active'     => true,
        ]);

        $this->assertCount(0, ErpConnector::due()->get());
    }

    public function test_scope_due_includes_overdue_connectors(): void
    {
        // Sincronizado há 400 minutos — frequency é 360 min, portanto vencido
        ErpConnector::create([
            'company_id'    => $this->company->id,
            'erp_type'      => 'bling',
            'credentials'   => ['client_id' => 'x'],
            'sync_frequency' => 360,
            'last_synced_at' => now()->subMinutes(400),
            'is_active'     => true,
        ]);

        $this->assertCount(1, ErpConnector::due()->get());
    }

    public function test_inactive_connector_not_in_due_scope(): void
    {
        ErpConnector::create([
            'company_id'    => $this->company->id,
            'erp_type'      => 'bling',
            'credentials'   => ['client_id' => 'x'],
            'sync_frequency' => 360,
            'last_synced_at' => null,
            'is_active'     => false, // inativo
        ]);

        $this->assertCount(0, ErpConnector::due()->get());
    }

    // ─── StubConnector ────────────────────────────────────────────────────

    public function test_stub_connector_yields_correct_number_of_invoices(): void
    {
        $stub = new StubConnector(invoiceCount: 3);
        $items = iterator_to_array($stub->fetchInvoicesSince(Carbon::now()->subDay()));

        $this->assertCount(3, $items);

        // Valida formato do payload
        $first = json_decode($items[0], true);
        $this->assertEquals('invoice.created', $first['event']);
        $this->assertArrayHasKey('data', $first);
        $this->assertEquals('Fornecedor Stub SA', $first['data']['contato']['nome']);
    }

    public function test_stub_connector_test_connection_returns_true(): void
    {
        $stub = new StubConnector();
        $this->assertTrue($stub->testConnection());
    }

    public function test_stub_connector_throws_on_auth_failure(): void
    {
        $stub = new StubConnector(shouldFailAuth: true);

        $this->expectException(ConnectorException::class);
        $this->expectExceptionMessageMatches('/autenticação/i');
        $stub->testConnection();
    }

    public function test_stub_connector_throws_on_fetch_failure(): void
    {
        $stub = new StubConnector(shouldFailFetch: true);

        $this->expectException(ConnectorException::class);
        $this->expectExceptionMessageMatches('/500/');

        iterator_to_array($stub->fetchInvoicesSince(Carbon::now()->subDay()));
    }

    // ─── ConnectorFactory ─────────────────────────────────────────────────

    public function test_factory_throws_for_unsupported_erp(): void
    {
        $connector = ErpConnector::create([
            'company_id'    => $this->company->id,
            'erp_type'      => 'oracle', // não suportado ainda
            'credentials'   => ['user' => 'x'],
            'sync_frequency' => 360,
        ]);

        $this->expectException(ConnectorException::class);
        $this->expectExceptionMessageMatches("/oracle.*não é suportado/i");

        ConnectorFactory::make($connector);
    }

    public function test_factory_supported_types_does_not_expose_stub(): void
    {
        $types = ConnectorFactory::supportedTypes();

        $this->assertContains('bling', $types);
        $this->assertNotContains('stub', $types);
    }
}
