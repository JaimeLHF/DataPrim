<?php

namespace Tests\Feature\DataLumen;

use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes do endpoint de Benchmark de Estrutura de Custo (DataLumen).
 *
 * Endpoint: GET /api/v1/dashboard/cost-structure-benchmark
 *
 * Cobre:
 * - 401 para requisições não autenticadas
 * - 200 para usuário autenticado
 * - Estrutura básica da resposta (meta, categories)
 * - Endpoint /periods retorna lista de períodos
 */
class BenchmarkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'                => 'Empresa Benchmark Teste',
            'cnpj'                => '22333444000177',
            'slug'                => 'empresa-benchmark-teste',
            'plan'                => 'enterprise',
            'is_active'           => true,
            'benchmark_segment'   => 'moveleiro',
            'benchmark_region'    => 'Sul',
        ]);

        $this->user = User::factory()->create();

        CompanyUser::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'role'       => 'admin',
            'joined_at'  => now(),
        ]);

        $this->token = $this->user->createToken('benchmark-test')->plainTextToken;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ];
    }
    /**
     * CostStructureBenchmarkService usa DATE_FORMAT (função MySQL).
     * Testes que dependem desse serviço são ignorados no SQLite (phpunit.xml).
     */
    private function skipIfSqlite(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Requer MySQL para suporte a DATE_FORMAT.');
        }
    }
    // ── Autenticação ──────────────────────────────────────────────────────────

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson(
            '/api/v1/dashboard/cost-structure-benchmark',
            ['Accept' => 'application/json']
        )->assertStatus(401);
    }

    // ── GET /api/v1/dashboard/cost-structure-benchmark ────────────────────────

    public function test_authenticated_user_can_access_benchmark_endpoint(): void
    {
        $this->skipIfSqlite();

        $this->getJson('/api/v1/dashboard/cost-structure-benchmark', $this->headers())
            ->assertStatus(200);
    }

    public function test_benchmark_response_contains_meta_and_categories_keys(): void
    {
        $this->skipIfSqlite();

        $response = $this->getJson('/api/v1/dashboard/cost-structure-benchmark', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure(['meta', 'categories']);
    }

    public function test_benchmark_returns_gracefully_when_no_data(): void
    {
        $this->skipIfSqlite();

        // Sem company_cost_structures ou market_benchmark_indexes,
        // o controller retorna meta com error e categories vazio
        $response = $this->getJson('/api/v1/dashboard/cost-structure-benchmark', $this->headers());

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertArrayHasKey('meta', $body);
        $this->assertArrayHasKey('categories', $body);
    }

    // ── GET /api/v1/dashboard/cost-structure-benchmark/periods ────────────────

    public function test_unauthenticated_request_to_periods_returns_401(): void
    {
        $this->getJson(
            '/api/v1/dashboard/cost-structure-benchmark/periods',
            ['Accept' => 'application/json']
        )->assertStatus(401);
    }

    public function test_authenticated_user_can_access_periods_endpoint(): void
    {
        $this->skipIfSqlite();

        $this->getJson(
            '/api/v1/dashboard/cost-structure-benchmark/periods',
            $this->headers()
        )->assertStatus(200);
    }

    public function test_periods_returns_list(): void
    {
        $this->skipIfSqlite();

        $response = $this->getJson(
            '/api/v1/dashboard/cost-structure-benchmark/periods',
            $this->headers()
        );

        $response->assertStatus(200)
            ->assertJsonIsArray();
    }

    // ── Filtros opcionais ────────────────────────────────────────────

    public function test_benchmark_accepts_period_and_region_filters(): void
    {
        $this->skipIfSqlite();

        $this->getJson(
            '/api/v1/dashboard/cost-structure-benchmark?period=2026-01&region=Sul',
            $this->headers()
        )->assertStatus(200);
    }
}
