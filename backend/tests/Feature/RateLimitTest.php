<?php

namespace Tests\Feature;

use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\User;
use App\DataCore\Models\WebhookConfig;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;
    private WebhookConfig $config;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Zera contadores de cache para evitar interferência entre testes
        Cache::flush();

        $this->company = Company::create([
            'name'      => 'Empresa Rate Limit',
            'cnpj'      => '12345678000190',
            'slug'      => 'empresa-rl',
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

        $secret = WebhookConfig::generateSecret();
        $this->config = WebhookConfig::create([
            'company_id' => $this->company->id,
            'erp_type'   => 'bling',
            'slug'       => 'empresa-rl-bling',
            'secret'     => $secret,
            'is_active'  => true,
        ]);
    }

    private function webhookUrl(string $slug = null): string
    {
        return '/api/v1/webhooks/receive/' . ($slug ?? $this->config->slug);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->adminToken}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ];
    }

    // ─── Webhook (público) ──────────────────────────────────────────────

    #[Test]
    public function test_webhook_returns_429_when_rate_limit_exceeded(): void
    {
        // Sobrescreve para 3 req/min para não precisar de 60 iterações
        RateLimiter::for('webhook', fn ($request) =>
            Limit::perMinute(3)->by($request->route('slug') ?? $request->ip())
        );

        $url = $this->webhookUrl();

        for ($i = 0; $i < 3; $i++) {
            $this->postJson($url, ['event' => 'test']);
        }

        $this->postJson($url, ['event' => 'test'])
            ->assertStatus(429)
            ->assertJsonFragment(['message' => 'Muitas requisições. Por favor, aguarde antes de tentar novamente.']);
    }

    #[Test]
    public function test_webhook_rate_limit_is_per_slug(): void
    {
        // Sobrescreve para 3 req/min
        RateLimiter::for('webhook', fn ($request) =>
            Limit::perMinute(3)->by($request->route('slug') ?? $request->ip())
        );

        // Segunda empresa com slug diferente
        $company2 = Company::create([
            'name'      => 'Empresa B',
            'cnpj'      => '98765432000100',
            'slug'      => 'empresa-b',
            'plan'      => 'starter',
            'is_active' => true,
        ]);
        $config2 = WebhookConfig::create([
            'company_id' => $company2->id,
            'erp_type'   => 'bling',
            'slug'       => 'empresa-b-bling',
            'secret'     => WebhookConfig::generateSecret(),
            'is_active'  => true,
        ]);

        $slug1 = $this->config->slug;
        $slug2 = $config2->slug;

        // Esgota o limite do slug1
        for ($i = 0; $i < 3; $i++) {
            $this->postJson($this->webhookUrl($slug1), ['event' => 'test']);
        }

        // slug1 deve estar throttled
        $this->postJson($this->webhookUrl($slug1), ['event' => 'test'])
            ->assertStatus(429);

        // slug2 NÃO deve estar throttled (chave de cache isolada)
        $response = $this->postJson($this->webhookUrl($slug2), ['event' => 'test']);
        $this->assertNotEquals(429, $response->getStatusCode(), 'slug2 não deve ser throttled pelo limite do slug1');
    }

    #[Test]
    public function test_webhook_response_includes_rate_limit_headers(): void
    {
        RateLimiter::for('webhook', fn ($request) =>
            Limit::perMinute(10)->by($request->route('slug') ?? $request->ip())
        );

        $response = $this->postJson($this->webhookUrl(), ['event' => 'test']);

        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    // ─── Rotas autenticadas ─────────────────────────────────────────────

    #[Test]
    public function test_authenticated_api_rate_limit(): void
    {
        // Sobrescreve para 3 req/min para evitar 120 iterações
        RateLimiter::for('api', fn ($request) =>
            Limit::perMinute(3)->by($request->user()?->id ?? $request->ip())
        );

        for ($i = 0; $i < 3; $i++) {
            $this->getJson('/api/v1/auth/me', $this->authHeaders());
        }

        $this->getJson('/api/v1/auth/me', $this->authHeaders())
            ->assertStatus(429)
            ->assertJsonFragment(['message' => 'Muitas requisições. Por favor, aguarde antes de tentar novamente.']);
    }

    // ─── Rotas admin ────────────────────────────────────────────────────

    #[Test]
    public function test_admin_routes_have_stricter_rate_limit(): void
    {
        // api-admin: 2/min  |  api: 5/min
        // Após 2 requests à rota admin → 429; rota não-admin ainda responde
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(5)->by($request->user()?->id ?? $request->ip());
        });
        RateLimiter::for('api-admin', function ($request) {
            return Limit::perMinute(2)->by('admin:' . ($request->user()?->id ?? $request->ip()));
        });

        // Esgota o limite admin (2 requests)
        for ($i = 0; $i < 2; $i++) {
            $this->getJson('/api/v1/invites', $this->authHeaders());
        }

        // 3ª request em rota admin deve ser throttled
        $this->getJson('/api/v1/invites', $this->authHeaders())
            ->assertStatus(429)
            ->assertJsonFragment(['message' => 'Muitas requisições. Por favor, aguarde antes de tentar novamente.']);

        // Rota não-admin (somente throttle:api, 5/min) ainda deve responder
        $response = $this->getJson('/api/v1/auth/me', $this->authHeaders());
        $this->assertNotEquals(429, $response->getStatusCode(), 'Rota auth:sanctum não deve ser throttled pelo limite admin');
    }

    // ─── Login (anti brute-force) ────────────────────────────────────────

    #[Test]
    public function test_login_returns_429_when_rate_limit_exceeded(): void
    {
        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(3)
                ->by($request->ip())
                ->response(function ($request, array $headers) {
                    return response()->json([
                        'message' => 'Muitas tentativas de login. Por favor, aguarde antes de tentar novamente.',
                    ], 429, $headers);
                });
        });

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'x@x.com', 'password' => 'wrong']);
        }

        $this->postJson('/api/v1/auth/login', ['email' => 'x@x.com', 'password' => 'wrong'])
            ->assertStatus(429)
            ->assertJsonFragment(['message' => 'Muitas tentativas de login. Por favor, aguarde antes de tentar novamente.']);
    }

    #[Test]
    public function test_login_rate_limit_includes_retry_after_header(): void
    {
        RateLimiter::for('login', fn ($request) =>
            Limit::perMinute(1)->by($request->ip())
        );

        $this->postJson('/api/v1/auth/login', ['email' => 'x@x.com', 'password' => 'wrong']);

        $this->postJson('/api/v1/auth/login', ['email' => 'x@x.com', 'password' => 'wrong'])
            ->assertStatus(429)
            ->assertHeader('Retry-After');
    }
}
