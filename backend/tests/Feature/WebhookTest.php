<?php

namespace Tests\Feature;

use App\DataBridge\Adapters\BlingAdapter;
use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\User;
use App\DataCore\Models\WebhookConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;
    private WebhookConfig $config;
    private string $secret;
    private string $adminToken;

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

        $this->secret = WebhookConfig::generateSecret();
        $this->config = WebhookConfig::create([
            'company_id' => $this->company->id,
            'erp_type'   => 'bling',
            'slug'       => 'moveis-ruiz-bling',
            'secret'     => $this->secret,
            'is_active'  => true,
        ]);
    }

    private function adminHeaders(array $extra = []): array
    {
        return array_merge([
            'Authorization' => "Bearer {$this->adminToken}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ], $extra);
    }

    private function blingArray(string $numero = '000001'): array
    {
        return [
            'eventId' => 'evt-' . $numero,
            'event'   => 'invoice.created',
            'data'    => [
                'id'          => (int) $numero,
                'numero'      => $numero,
                'serie'       => '1',
                'dataEmissao' => '2026-01-15 10:00:00',
                'contato'     => [
                    'nome'             => 'Fornecedor Exemplo SA',
                    'numeroDocumento'  => '98.765.432/0001-55',
                    'uf'               => 'PR',
                ],
                'itens' => [
                    ['descricao' => 'MDF 18mm Branco', 'quantidade' => 100, 'valor' => 45.50],
                ],
                'totalProdutos' => 4550.00,
                'totalFrete'    => 200.00,
                'totalImpostos' => 386.75,
                'total'         => 5136.75,
            ],
        ];
    }

    /**
     * Calcula a assinatura HMAC exatamente como o controller vai verificar.
     *
     * O Laravel, ao fazer postJson($array), serializa com json_encode($array, JSON_UNESCAPED_UNICODE).
     * O controller lê $request->getContent() — que é esse mesmo body string.
     * Calculamos o HMAC sobre json_encode($array) para que os hashes coincidam.
     */
    private function blingSignature(array $payload): string
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        return 'sha256=' . hash_hmac('sha256', $body, $this->secret);
    }

    /**
     * Envia webhook autenticado e retorna a resposta.
     */
    private function postWebhook(array $payload, ?string $signature = null): \Illuminate\Testing\TestResponse
    {
        $sig = $signature ?? $this->blingSignature($payload);
        return $this->withHeaders(['X-Bling-Signature-256' => $sig])
            ->postJson("/api/v1/webhooks/receive/{$this->config->slug}", $payload);
    }

    // ─── Endpoint público ─────────────────────────────────────────────────

    public function test_invalid_slug_returns_404(): void
    {
        $this->postJson('/api/v1/webhooks/receive/slug-inexistente', $this->blingArray())
            ->assertStatus(404)
            ->assertJsonFragment(['error' => 'Webhook não encontrado.']);
    }

    public function test_invalid_hmac_signature_returns_401(): void
    {
        $this->withHeaders(['X-Bling-Signature-256' => 'sha256=assinatura_errada'])
            ->postJson("/api/v1/webhooks/receive/{$this->config->slug}", $this->blingArray())
            ->assertStatus(401)
            ->assertJsonFragment(['error' => 'Assinatura inválida.']);
    }

    public function test_missing_signature_returns_401(): void
    {
        // Sem header — validateSignature() recebe '' que não começa com sha256=
        $this->postJson("/api/v1/webhooks/receive/{$this->config->slug}", $this->blingArray())
            ->assertStatus(401);
    }

    public function test_valid_bling_webhook_is_accepted(): void
    {
        Queue::fake();

        $payload = $this->blingArray();
        $this->postWebhook($payload)
            ->assertStatus(202)
            ->assertJsonFragment(['status' => 'pending']);

        $this->assertDatabaseHas('raw_ingestions', [
            'company_id' => $this->company->id,
            'channel'    => 'webhook',
            'source'     => 'bling',
            'status'     => 'pending',
        ]);
    }

    public function test_duplicate_webhook_payload_is_skipped(): void
    {
        Queue::fake();

        $payload = $this->blingArray();

        // Primeira chamada
        $this->postWebhook($payload)->assertStatus(202);

        // Segunda chamada (mesmo payload → mesmo hash SHA-256)
        $response = $this->postWebhook($payload)->assertStatus(202);

        $this->assertTrue($response->json('skipped'));
        $this->assertDatabaseCount('raw_ingestions', 1);
    }

    public function test_bling_payload_is_normalized_and_persisted(): void
    {
        // QUEUE_CONNECTION=sync → job roda imediatamente
        $payload = $this->blingArray('000099');
        $this->postWebhook($payload)->assertStatus(202);

        $this->assertDatabaseHas('invoices', [
            'company_id'     => $this->company->id,
            'invoice_number' => 'BLING-000099-1',
            'source_system'  => 'bling',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'company_id' => $this->company->id,
            'cnpj'       => '98765432000155',
            'name'       => 'Fornecedor Exemplo SA',
            'region'     => 'Sul',
        ]);
    }

    public function test_inactive_webhook_config_returns_404(): void
    {
        $this->config->update(['is_active' => false]);

        $this->postWebhook($this->blingArray())->assertStatus(404);
    }

    // ─── CRUD de configurações ─────────────────────────────────────────────

    public function test_admin_can_create_webhook_config(): void
    {
        $this->config->delete();

        $response = $this->postJson('/api/v1/webhook-configs', ['erp_type' => 'bling'], $this->adminHeaders());

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'erp_type', 'slug', 'secret', 'webhook_url', 'created_at']])
            ->assertJsonPath('data.erp_type', 'bling');

        $this->assertNotEmpty($response->json('data.secret'));
        $this->assertStringContainsString('webhooks/receive/', $response->json('data.webhook_url'));
    }

    public function test_cannot_create_duplicate_erp_type_for_same_company(): void
    {
        // Já existe 'bling' criado no setUp
        $this->postJson('/api/v1/webhook-configs', ['erp_type' => 'bling'], $this->adminHeaders())
            ->assertStatus(409);
    }

    public function test_admin_can_list_webhook_configs(): void
    {
        $this->getJson('/api/v1/webhook-configs', $this->adminHeaders())
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.erp_type', 'bling');
    }

    public function test_admin_can_delete_webhook_config(): void
    {
        $this->deleteJson("/api/v1/webhook-configs/{$this->config->id}", [], $this->adminHeaders())
            ->assertStatus(204);

        $this->assertDatabaseMissing('webhook_configs', ['id' => $this->config->id]);
    }

    // ─── Webhook logs ──────────────────────────────────────────────────────

    public function test_can_list_webhook_logs(): void
    {
        Queue::fake();

        // Cria um ingestion via webhook
        $this->postWebhook($this->blingArray());

        $response = $this->getJson('/api/v1/webhooks/logs', $this->adminHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.source', 'bling')
            ->assertJsonPath('data.0.status', 'pending');
    }

    // ─── BlingAdapter unit-style tests ────────────────────────────────────

    public function test_bling_adapter_validates_correct_hmac(): void
    {
        $adapter = app(BlingAdapter::class);
        $body    = json_encode($this->blingArray(), JSON_UNESCAPED_UNICODE);
        $sig     = 'sha256=' . hash_hmac('sha256', $body, $this->secret);

        $this->assertTrue($adapter->validateSignature($body, $sig, $this->secret));
    }

    public function test_bling_adapter_rejects_wrong_hmac(): void
    {
        $adapter = app(BlingAdapter::class);
        $body    = json_encode($this->blingArray(), JSON_UNESCAPED_UNICODE);

        $this->assertFalse($adapter->validateSignature($body, 'sha256=errado', $this->secret));
        $this->assertFalse($adapter->validateSignature($body, 'sem-prefixo', $this->secret));
    }
}
