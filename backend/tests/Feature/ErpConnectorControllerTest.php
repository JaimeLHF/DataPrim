<?php

namespace Tests\Feature;

use App\DataBridge\Jobs\SyncErpConnectorJob;
use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\ErpConnector;
use App\DataCore\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Testes da Fase 4c — ErpConnectorController (API REST).
 *
 * Cobre: listagem, criação (com testConnection), remoção, sync manual e teste de conexão.
 * Usa Http::fake() para o testConnection e Queue::fake() para o SyncErpConnectorJob.
 */
class ErpConnectorControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User    $admin;
    private string  $token;
    private array   $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Conectores',
            'cnpj'      => '22333444000195',
            'slug'      => 'empresa-conectores',
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

        $this->token = $this->admin->createToken('test')->plainTextToken;

        $this->headers = [
            'Authorization' => "Bearer {$this->token}",
            'X-Company-Id'  => (string) $this->company->id,
            'Accept'        => 'application/json',
        ];
    }

    // ─── Helper ───────────────────────────────────────────────────────────

    private function fakeBlingTokenOk(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'tok-ok'], 200),
        ]);
    }

    private function fakeBlingTokenFail(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['error' => 'invalid_client'], 401),
        ]);
    }

    private function createConnector(array $overrides = []): ErpConnector
    {
        return ErpConnector::create(array_merge([
            'company_id'     => $this->company->id,
            'erp_type'       => 'bling',
            'credentials'    => ['client_id' => 'x', 'client_secret' => 'y'],
            'sync_frequency' => 360,
            'is_active'      => true,
        ], $overrides));
    }

    // ─── index ────────────────────────────────────────────────────────────

    public function test_index_returns_empty_list_initially(): void
    {
        $res = $this->getJson('/api/v1/erp-connectors', $this->headers);

        $res->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_index_returns_connectors_for_company(): void
    {
        $this->createConnector(['last_sync_status' => 'ok']);

        $res = $this->getJson('/api/v1/erp-connectors', $this->headers);

        $res->assertStatus(200);
        $data = $res->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('bling', $data[0]['erp_type']);
        $this->assertEquals('ok', $data[0]['last_sync_status']);
        $this->assertArrayNotHasKey('credentials', $data[0]);
    }

    // ─── store ────────────────────────────────────────────────────────────

    public function test_store_creates_connector_and_dispatches_first_sync(): void
    {
        Queue::fake();
        $this->fakeBlingTokenOk();

        $res = $this->postJson('/api/v1/erp-connectors', [
            'erp_type'       => 'bling',
            'credentials'    => ['client_id' => 'abc', 'client_secret' => 'xyz'],
            'sync_frequency' => 360,
        ], $this->headers);

        $res->assertStatus(201);
        $this->assertDatabaseHas('erp_connectors', [
            'company_id' => $this->company->id,
            'erp_type'   => 'bling',
        ]);
        Queue::assertPushed(SyncErpConnectorJob::class);
    }

    public function test_store_returns_422_when_credentials_invalid(): void
    {
        $this->fakeBlingTokenFail();

        $res = $this->postJson('/api/v1/erp-connectors', [
            'erp_type'    => 'bling',
            'credentials' => ['client_id' => 'bad', 'client_secret' => 'bad'],
        ], $this->headers);

        $res->assertStatus(422)
            ->assertJsonPath('error', 'Falha de conexão.');

        $this->assertDatabaseCount('erp_connectors', 0);
    }

    public function test_store_returns_409_when_connector_already_exists(): void
    {
        Queue::fake();
        $this->fakeBlingTokenOk();
        $this->createConnector();

        $res = $this->postJson('/api/v1/erp-connectors', [
            'erp_type'    => 'bling',
            'credentials' => ['client_id' => 'abc', 'client_secret' => 'xyz'],
        ], $this->headers);

        $res->assertStatus(409)
            ->assertJsonPath('error', 'Conector já existe.');
    }

    public function test_store_validates_erp_type(): void
    {
        $res = $this->postJson('/api/v1/erp-connectors', [
            'erp_type'    => 'oracle', // não suportado
            'credentials' => ['user' => 'x'],
        ], $this->headers);

        $res->assertStatus(422);
    }

    public function test_store_response_never_exposes_credentials(): void
    {
        Queue::fake();
        $this->fakeBlingTokenOk();

        $res = $this->postJson('/api/v1/erp-connectors', [
            'erp_type'    => 'bling',
            'credentials' => ['client_id' => 'abc', 'client_secret' => 'xyz'],
        ], $this->headers);

        $res->assertStatus(201);
        $this->assertArrayNotHasKey('credentials', $res->json('data'));
    }

    // ─── destroy ──────────────────────────────────────────────────────────

    public function test_destroy_removes_connector(): void
    {
        $connector = $this->createConnector();

        $this->deleteJson("/api/v1/erp-connectors/{$connector->id}", [], $this->headers)
            ->assertStatus(204);

        $this->assertDatabaseMissing('erp_connectors', ['id' => $connector->id]);
    }

    public function test_destroy_returns_404_for_other_company_connector(): void
    {
        $otherCompany = Company::create([
            'name' => 'Outra',
            'cnpj' => '99888777000166',
            'slug' => 'outra',
            'plan' => 'starter',
            'is_active' => true,
        ]);
        $connector = ErpConnector::create([
            'company_id'  => $otherCompany->id,
            'erp_type'    => 'bling',
            'credentials' => ['client_id' => 'x'],
        ]);

        $this->deleteJson("/api/v1/erp-connectors/{$connector->id}", [], $this->headers)
            ->assertStatus(404);
    }

    // ─── sync manual ──────────────────────────────────────────────────────

    public function test_sync_dispatches_job_and_returns_202(): void
    {
        Queue::fake();
        $connector = $this->createConnector();

        $this->postJson("/api/v1/erp-connectors/{$connector->id}/sync", [], $this->headers)
            ->assertStatus(202)
            ->assertJsonPath('connector_id', $connector->id);

        Queue::assertPushed(SyncErpConnectorJob::class);
    }

    // ─── testConnection ───────────────────────────────────────────────────

    public function test_test_connection_returns_ok_true_on_success(): void
    {
        $this->fakeBlingTokenOk();

        $this->postJson('/api/v1/erp-connectors/test-connection', [
            'erp_type'    => 'bling',
            'credentials' => ['client_id' => 'abc', 'client_secret' => 'xyz'],
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('ok', true);
    }

    public function test_test_connection_returns_ok_false_on_failure(): void
    {
        $this->fakeBlingTokenFail();

        $this->postJson('/api/v1/erp-connectors/test-connection', [
            'erp_type'    => 'bling',
            'credentials' => ['client_id' => 'bad', 'client_secret' => 'bad'],
        ], $this->headers)
            ->assertStatus(422)
            ->assertJsonPath('ok', false);
    }

    // ─── Auth ─────────────────────────────────────────────────────────────

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/erp-connectors')->assertStatus(401);
    }
}
