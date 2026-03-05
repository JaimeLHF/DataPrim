<?php

namespace Tests\Feature;

use App\DataBridge\Jobs\ProcessIngestionJob;
use App\DataBridge\Jobs\SyncErpConnectorJob;
use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\ErpConnector;
use App\DataCore\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Testes da Fase 4b — BlingConnector + SyncErpConnectorJob.
 *
 * Usa Http::fake() para mockar a API do Bling — sem chamadas reais.
 */
class ErpConnectorSyncTest extends TestCase
{
    use RefreshDatabase;

    private Company      $company;
    private User         $admin;
    private ErpConnector $connector;
    private string       $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Móveis Ruiz',
            'cnpj'      => '12345678000190',
            'slug'      => 'moveis-ruiz',
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

        $this->connector = ErpConnector::create([
            'company_id'     => $this->company->id,
            'erp_type'       => 'bling',
            'credentials'    => ['client_id' => 'fake-id', 'client_secret' => 'fake-secret'],
            'sync_frequency' => 360,
            'is_active'      => true,
            // Token pré-autorizado para que fetchInvoicesSince() funcione sem OAuth real
            'access_token'     => encrypt('fake-access-token'),
            'refresh_token'    => encrypt('fake-refresh-token'),
            'token_expires_at' => now()->addHour(),
            // Cursor recente: job usa since=now()-1h → apenas 1 janela de 30 dias → poucos fakes necessários
            'last_synced_at'   => now()->subHour(),
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function fakeBlingToken(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'access_token' => 'fake-token-123',
                'expires_in'   => 3600,
            ], 200),
        ]);
    }

    private function fakeBlingNfes(array $nfes): void
    {
        // fetchInvoicesSince() faz: GET /nfe (listing) → GET /nfe/{id} (detalhe) → GET /nfe (page 2 vazia)
        $sequence = Http::sequence()
            ->push(['data' => $nfes], 200);     // página 1 — listagem

        foreach ($nfes as $nfe) {
            $sequence->push(['data' => $nfe], 200); // detalhe de cada NF-e
        }

        $sequence->push(['data' => []], 200);   // página 2 — vazia → fim

        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'fake-token-123'], 200),
            '*/nfe*'        => $sequence,
        ]);
    }

    private function blingNfe(string $numero = '000001'): array
    {
        return [
            'id'          => (int) $numero,
            'numero'      => $numero,
            'serie'       => '1',
            'dataEmissao' => '2026-01-15 10:00:00',
            'contato'     => [
                'nome'            => 'Fornecedor Connector SA',
                'numeroDocumento' => '98765432000155',
                'uf'              => 'SC',
            ],
            'itens' => [
                ['descricao' => 'Produto Teste', 'quantidade' => 5, 'valor' => 200.00],
            ],
            'totalProdutos' => 1000.00,
            'totalFrete'    => 50.00,
            'totalImpostos' => 85.00,
            'total'         => 1135.00,
        ];
    }

    // ─── BlingConnector: testConnection() ────────────────────────────────

    public function test_bling_connector_test_connection_success(): void
    {
        // Usa o conector com token armazenado — testConnection() valida o token sem probe HTTP
        $connector = new \App\DataBridge\Connectors\BlingConnector($this->connector);

        $this->assertTrue($connector->testConnection());
    }

    public function test_bling_connector_test_connection_fails_with_invalid_creds(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $connector = new \App\DataBridge\Connectors\BlingConnector(
            ['client_id' => 'wrong', 'client_secret' => 'wrong']
        );

        $this->expectException(\App\DataCore\Exceptions\ConnectorException::class);
        $this->expectExceptionMessageMatches('/autenticação/i');

        $connector->testConnection();
    }

    // ─── BlingConnector: fetchInvoicesSince() ─────────────────────────────

    public function test_bling_connector_fetches_and_yields_invoices(): void
    {
        $this->fakeBlingNfes([$this->blingNfe('111111'), $this->blingNfe('222222')]);

        $connector = new \App\DataBridge\Connectors\BlingConnector($this->connector);

        $payloads = iterator_to_array(
            $connector->fetchInvoicesSince(Carbon::now()->subDay())
        );

        $this->assertCount(2, $payloads);

        $first = json_decode($payloads[0], true);
        $this->assertEquals('invoice.created', $first['event']);
        $this->assertEquals('111111', $first['data']['numero']);
    }

    // ─── SyncErpConnectorJob ──────────────────────────────────────────────

    public function test_sync_job_creates_raw_ingestions_and_dispatches_process_job(): void
    {
        // Sem Queue::fake() — o job executa de verdade
        // Queue::fake() no ProcessIngestionJob para não normalizar (só verificar o raw)
        Queue::fake([ProcessIngestionJob::class]);
        $this->fakeBlingNfes([$this->blingNfe('333333')]);

        SyncErpConnectorJob::dispatchSync($this->connector->id);

        $this->assertDatabaseHas('raw_ingestions', [
            'company_id' => $this->company->id,
            'channel'    => 'connector',
            'source'     => 'bling',
            'status'     => 'pending',
        ]);

        Queue::assertPushed(ProcessIngestionJob::class);
    }

    public function test_sync_job_updates_last_synced_at_on_success(): void
    {
        // Sem NF-es para não disparar ProcessIngestionJob
        $this->fakeBlingNfes([]);

        $lastSyncBefore = $this->connector->last_synced_at;

        SyncErpConnectorJob::dispatchSync($this->connector->id);

        $this->connector->refresh();
        $this->assertEquals('ok', $this->connector->last_sync_status);
        $this->assertNotNull($this->connector->last_synced_at);
        // last_synced_at deve ter sido atualizado pelo job
        $this->assertTrue($this->connector->last_synced_at->gte($lastSyncBefore));
    }

    public function test_sync_job_deduplicates_payloads(): void
    {
        // Sem Queue::fake() geral — o job precisa executar para criar o raw_ingestion
        // Faz fake apenas do ProcessIngestionJob para não tentar normalizar
        Queue::fake([ProcessIngestionJob::class]);

        $nfe = $this->blingNfe('444444');

        // Sequência: listing1 → detalhe → listing2 vazia | listing1 → detalhe → listing2 vazia
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'fake-token'], 200),
            '*/nfe*'        => Http::sequence()
                ->push(['data' => [$nfe]], 200)  // sync 1 - listagem page 1
                ->push(['data' => $nfe],   200)  // sync 1 - detalhe do NF-e
                ->push(['data' => []],     200)  // sync 1 - listagem page 2 (fim)
                ->push(['data' => [$nfe]], 200)  // sync 2 - listagem page 1 (mesmo payload)
                ->push(['data' => $nfe],   200)  // sync 2 - detalhe do NF-e
                ->push(['data' => []],     200), // sync 2 - listagem page 2 (fim)
        ]);

        SyncErpConnectorJob::dispatchSync($this->connector->id);
        SyncErpConnectorJob::dispatchSync($this->connector->id);

        // Só 1 raw_ingestion criado — hash SHA-256 idêntico → segundo ignorado
        $this->assertDatabaseCount('raw_ingestions', 1);
    }

    public function test_sync_job_normalizes_invoice_end_to_end(): void
    {
        // Queue em sync mode → ProcessIngestionJob roda imediatamente
        $this->fakeBlingNfes([$this->blingNfe('555555')]);

        SyncErpConnectorJob::dispatchSync($this->connector->id);

        $this->assertDatabaseHas('invoices', [
            'company_id'     => $this->company->id,
            'invoice_number' => 'BLING-555555-1',
            'source_system'  => 'bling',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'company_id' => $this->company->id,
            'cnpj'       => '98765432000155',
            'region'     => 'Sul',              // SC → Sul
        ]);
    }

    public function test_sync_job_marks_error_on_auth_failure(): void
    {
        // Expira o token para que o refresh seja tentado — e falhe com 401
        $this->connector->update(['token_expires_at' => now()->subHour()]);

        Http::fake([
            '*/oauth/token' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        // Esgota tentativas via dispatchSync (modo síncrono não faz retry)
        try {
            SyncErpConnectorJob::dispatchSync($this->connector->id);
        } catch (\App\DataCore\Exceptions\ConnectorException $e) {
            // Esperado
        }

        // failed() deve ter sido chamado e gravado o erro
        $this->connector->refresh();
        $this->assertEquals('error', $this->connector->last_sync_status);
        $this->assertNotNull($this->connector->last_sync_error);
    }

    public function test_inactive_connector_is_skipped_by_sync_job(): void
    {
        Queue::fake();
        $this->connector->update(['is_active' => false]);

        // Não deve lançar exceção, apenas retornar silenciosamente
        SyncErpConnectorJob::dispatchSync($this->connector->id);

        $this->assertDatabaseCount('raw_ingestions', 0);
    }

    // ─── Comando artisan erp:sync ────────────────────────────────────────

    public function test_erp_sync_command_dispatches_jobs_for_due_connectors(): void
    {
        Queue::fake();

        // Simula conector vencido (last_synced_at há 400 min, frequency 360)
        $this->connector->update(['last_synced_at' => now()->subMinutes(400)]);

        $this->artisan('erp:sync')->assertExitCode(0);

        Queue::assertPushed(SyncErpConnectorJob::class, 1);
    }

    public function test_erp_sync_command_skips_recently_synced_connectors(): void
    {
        Queue::fake();

        // Sincronizado há 5 minutos — não deve disparar
        $this->connector->update(['last_synced_at' => now()->subMinutes(5)]);

        $this->artisan('erp:sync')
            ->expectsOutput('Nenhum conector ERP pendente de sincronização.')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }
}
