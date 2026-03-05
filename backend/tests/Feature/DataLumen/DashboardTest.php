<?php

namespace Tests\Feature\DataLumen;

use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes dos endpoints do Dashboard (DataLumen).
 *
 * Cobre:
 * - Autenticação (401 sem token)
 * - Acesso aos KPIs principais
 * - Acesso aos demais endpoints analíticos
 * - Estrutura das respostas
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Dashboard Teste',
            'cnpj'      => '12345678000190',
            'slug'      => 'empresa-dashboard-teste',
            'plan'      => 'pro',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create();

        CompanyUser::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'role'       => 'admin',
            'joined_at'  => now(),
        ]);

        $this->token = $this->user->createToken('dashboard-test')->plainTextToken;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function skipIfSqlite(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Teste requer MySQL (usa DATE_FORMAT).');
        }
    }

    private function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'X-Company-Id'  => $this->company->id,
            'Accept'        => 'application/json',
        ];
    }

    // ── KPIs ─────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_kpis(): void
    {
        $response = $this->getJson('/api/v1/dashboard/kpis', $this->headers());

        $response->assertStatus(200);
    }

    public function test_unauthenticated_request_to_kpis_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/kpis', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    public function test_kpis_response_contains_expected_keys(): void
    {
        $response = $this->getJson('/api/v1/dashboard/kpis', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'overall_tco_average',
                'market_variation_percent',
                'freight_weight_percent',
                'top_category',
                'benchmark_by_category',
            ]);
    }

    // ── TCO Breakdown ─────────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_tco_breakdown(): void
    {
        $this->getJson('/api/v1/dashboard/tco-breakdown', $this->headers())
            ->assertStatus(200);
    }

    public function test_unauthenticated_request_to_tco_breakdown_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/tco-breakdown', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    public function test_tco_breakdown_response_contains_expected_keys(): void
    {
        $this->getJson('/api/v1/dashboard/tco-breakdown', $this->headers())
            ->assertStatus(200)
            ->assertJsonStructure(['company', 'market']);
    }

    // ── Price Evolution ───────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_price_evolution(): void
    {
        $this->skipIfSqlite();

        $this->getJson('/api/v1/dashboard/price-evolution', $this->headers())
            ->assertStatus(200);
    }

    public function test_unauthenticated_request_to_price_evolution_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/price-evolution', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    // ── Price Index ───────────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_price_index(): void
    {
        $this->skipIfSqlite();

        $this->getJson('/api/v1/dashboard/price-index', $this->headers())
            ->assertStatus(200);
    }

    public function test_unauthenticated_request_to_price_index_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/price-index', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    // ── Price Dispersion ──────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_price_dispersion(): void
    {
        $this->getJson('/api/v1/dashboard/dispersion', $this->headers())
            ->assertStatus(200);
    }

    public function test_unauthenticated_request_to_dispersion_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/dispersion', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    // ── Freight Impact ────────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_freight_impact(): void
    {
        $this->getJson('/api/v1/dashboard/freight-impact', $this->headers())
            ->assertStatus(200);
    }

    public function test_unauthenticated_request_to_freight_impact_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/freight-impact', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    // ── Category Ranking ──────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_category_ranking(): void
    {
        $this->getJson('/api/v1/dashboard/category-ranking', $this->headers())
            ->assertStatus(200);
    }

    public function test_unauthenticated_request_to_category_ranking_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/category-ranking', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    // ── Gross vs Net ──────────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_gross_vs_net(): void
    {
        $this->getJson('/api/v1/dashboard/gross-vs-net', $this->headers())
            ->assertStatus(200);
    }

    public function test_unauthenticated_request_to_gross_vs_net_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/gross-vs-net', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    // ── Filtros de período ────────────────────────────────────────────────────

    public function test_kpis_accepts_date_range_filters(): void
    {
        $this->getJson(
            '/api/v1/dashboard/kpis?start_date=2026-01-01&end_date=2026-12-31',
            $this->headers()
        )->assertStatus(200);
    }
}
