<?php

namespace Tests\Feature\DataLumen;

use App\DataCore\Models\Company;
use App\DataCore\Models\CompanyUser;
use App\DataCore\Models\Invoice;
use App\DataCore\Models\InvoiceItem;
use App\DataCore\Models\Supplier;
use App\DataCore\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes do endpoint de Saving & Cost Avoidance (DataLumen).
 *
 * Nota: saving é calculado dinamicamente a partir dos invoice_items — não há
 * recurso gravável. O endpoint disponível é somente GET /api/v1/dashboard/saving.
 *
 * Cobre:
 * - 401 para requisições não autenticadas
 * - 200 para usuário autenticado (sem dados → totais zerados)
 * - 200 para usuário autenticado com dados de compra
 * - Estrutura da resposta (total_saving, total_cost_avoid, period_label, categories)
 */
class SavingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Saving Teste',
            'cnpj'      => '11222333000155',
            'slug'      => 'empresa-saving-teste',
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

        $this->token = $this->user->createToken('saving-test')->plainTextToken;
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

    // ── Autenticação ──────────────────────────────────────────────────────────

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/dashboard/saving', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    // ── GET /api/v1/dashboard/saving ──────────────────────────────────────────

    public function test_authenticated_user_can_access_saving_endpoint(): void
    {
        $this->getJson('/api/v1/dashboard/saving', $this->headers())
            ->assertStatus(200);
    }

    public function test_saving_response_contains_expected_structure(): void
    {
        $response = $this->getJson('/api/v1/dashboard/saving', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_saving',
                'total_cost_avoid',
                'period_label',
                'categories',
            ]);
    }

    public function test_saving_returns_zero_totals_when_no_invoices(): void
    {
        $response = $this->getJson('/api/v1/dashboard/saving', $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('total_saving', 0)
            ->assertJsonPath('total_cost_avoid', 0)
            ->assertJsonPath('categories', []);
    }

    public function test_saving_returns_data_when_invoices_exist(): void
    {
        // Cria um fornecedor e faturas dos últimos 6 meses para gerar saving
        $supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name'       => 'Fornecedor Saving SA',
            'cnpj'       => '98765432000111',
            'state'      => 'PR',
            'region'     => 'Sul',
        ]);

        // Fatura do período anterior (4 meses atrás)
        $invoicePrev = Invoice::create([
            'company_id'     => $this->company->id,
            'supplier_id'    => $supplier->id,
            'invoice_number' => 'NF-SAV-001',
            'issue_date'     => now()->subMonths(4)->format('Y-m-d'),
            'total_value'    => 4800.00,
            'freight_value'  => 200.00,
            'tax_value'      => 386.75,
        ]);

        InvoiceItem::create([
            'invoice_id'  => $invoicePrev->id,
            'product_description' => 'MDF 18mm',
            'category'    => 'MDF',
            'quantity'    => 100,
            'unit_price'  => 48.00,
            'total_price' => 4800.00,
        ]);

        // Fatura do período atual (1 mês atrás) — preço menor indica saving
        $invoiceCurr = Invoice::create([
            'company_id'     => $this->company->id,
            'supplier_id'    => $supplier->id,
            'invoice_number' => 'NF-SAV-002',
            'issue_date'     => now()->subMonth()->format('Y-m-d'),
            'total_value'    => 4550.00,
            'freight_value'  => 200.00,
            'tax_value'      => 386.75,
        ]);

        InvoiceItem::create([
            'invoice_id'  => $invoiceCurr->id,
            'product_description' => 'MDF 18mm',
            'category'    => 'MDF',
            'quantity'    => 100,
            'unit_price'  => 45.50,
            'total_price' => 4550.00,
        ]);

        $response = $this->getJson('/api/v1/dashboard/saving', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_saving',
                'total_cost_avoid',
                'period_label',
                'categories' => [
                    '*' => [
                        'category',
                        'prev_avg_price',
                        'curr_avg_price',
                        'saving_abs',
                    ],
                ],
            ]);

        // Deve ter pelo menos a categoria MDF
        $categories = collect($response->json('categories'));
        $this->assertTrue($categories->firstWhere('category', 'MDF') !== null);
    }
}
