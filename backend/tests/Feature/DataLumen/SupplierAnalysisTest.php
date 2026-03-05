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
 * Testes dos endpoints de Análise de Fornecedores (DataLumen).
 *
 * Endpoints:
 * - GET /api/v1/suppliers        → lista com métricas (filtragem por company)
 * - GET /api/v1/suppliers/{id}   → detalhe de um fornecedor
 *
 * Cobre:
 * - 401 sem autenticação
 * - 200 com usuário autenticado
 * - Estrutura da resposta {data: [...]}
 * - Isolamento multi-tenant: índex retorna somente fornecedores
 *   que têm invoices da empresa atual
 */
class SupplierAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;
    private string $token;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Supplier Teste',
            'cnpj'      => '33444555000188',
            'slug'      => 'empresa-supplier-teste',
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

        $this->token = $this->user->createToken('supplier-test')->plainTextToken;

        // Cria um fornecedor com invoice vinculada à empresa
        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'name'       => 'Fornecedor Principal SA',
            'cnpj'       => '98765432000155',
            'state'      => 'SC',
            'region'     => 'Sul',
        ]);

        $invoice = Invoice::create([
            'company_id'     => $this->company->id,
            'supplier_id'    => $this->supplier->id,
            'invoice_number' => 'NF-SUP-001',
            'issue_date'     => now()->subMonth()->format('Y-m-d'),
            'total_value'    => 5000.00,
            'freight_value'  => 300.00,
            'tax_value'      => 405.00,
        ]);

        InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'product_description' => 'MDF 18mm Branco',
            'category'    => 'MDF',
            'quantity'    => 100,
            'unit_price'  => 45.00,
            'total_price' => 4500.00,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * SupplierAnalysisController::show() usa DATE_FORMAT no monthly_evolution query.
     * Testes que chamam show() são ignorados no SQLite (phpunit.xml).
     */
    private function skipIfSqlite(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Requer MySQL para suporte a DATE_FORMAT em monthly_evolution.');
        }
    }

    private function headers(?string $token = null, ?int $companyId = null): array
    {
        return [
            'Authorization' => 'Bearer ' . ($token ?? $this->token),
            'X-Company-Id'  => $companyId ?? $this->company->id,
            'Accept'        => 'application/json',
        ];
    }

    // ── Autenticação ──────────────────────────────────────────────────────────

    public function test_unauthenticated_request_to_index_returns_401(): void
    {
        $this->getJson('/api/v1/suppliers', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    public function test_unauthenticated_request_to_show_returns_401(): void
    {
        $this->getJson("/api/v1/suppliers/{$this->supplier->id}", ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    // ── GET /api/v1/suppliers ─────────────────────────────────────────────────

    public function test_authenticated_user_can_access_suppliers_index(): void
    {
        $this->getJson('/api/v1/suppliers', $this->headers())
            ->assertStatus(200);
    }

    public function test_suppliers_index_returns_data_key(): void
    {
        $response = $this->getJson('/api/v1/suppliers', $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_suppliers_index_returns_company_suppliers(): void
    {
        $response = $this->getJson('/api/v1/suppliers', $this->headers());

        $data = collect($response->json('data'));

        // O fornecedor criado no setUp deve aparecer
        $found = $data->firstWhere('supplier_id', $this->supplier->id);
        $this->assertNotNull($found, 'Fornecedor com invoice da empresa deve aparecer no índice');
        $this->assertEquals('Fornecedor Principal SA', $found['supplier_name']);
    }

    public function test_suppliers_index_does_not_show_other_company_suppliers(): void
    {
        // Cria outra empresa com seu próprio fornecedor e invoice
        $otherCompany = Company::create([
            'name'      => 'Outra Empresa',
            'cnpj'      => '77888999000166',
            'slug'      => 'outra-empresa',
            'plan'      => 'starter',
            'is_active' => true,
        ]);

        $otherSupplier = Supplier::create([
            'company_id' => $otherCompany->id,
            'name'       => 'Fornecedor da Outra Empresa',
            'cnpj'       => '11223344000100',
            'state'      => 'SP',
            'region'     => 'Sudeste',
        ]);

        Invoice::create([
            'company_id'     => $otherCompany->id,
            'supplier_id'    => $otherSupplier->id,
            'invoice_number' => 'NF-OTHER-001',
            'issue_date'     => now()->subMonth()->format('Y-m-d'),
            'total_value'    => 3000.00,
            'freight_value'  => 150.00,
            'tax_value'      => 240.00,
        ]);

        // Acessa com credenciais da empresa original
        $response = $this->getJson('/api/v1/suppliers', $this->headers());
        $data     = collect($response->json('data'));

        // Fornecedor da outra empresa NÃO deve aparecer (invoice filtrada por company_id)
        $found = $data->firstWhere('supplier_id', $otherSupplier->id);
        $this->assertNull($found, 'Fornecedor de outra empresa não deve aparecer no índice');
    }

    // ── GET /api/v1/suppliers/{id} ────────────────────────────────────────────

    public function test_authenticated_user_can_access_supplier_show(): void
    {
        $this->skipIfSqlite();

        $response = $this->getJson("/api/v1/suppliers/{$this->supplier->id}", $this->headers());

        $response->assertStatus(200)
            ->assertJsonStructure(['supplier', 'categories', 'monthly_evolution']);
    }

    public function test_supplier_show_returns_correct_supplier_data(): void
    {
        $this->skipIfSqlite();

        $response = $this->getJson("/api/v1/suppliers/{$this->supplier->id}", $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('supplier.id', $this->supplier->id)
            ->assertJsonPath('supplier.name', 'Fornecedor Principal SA');
    }

    public function test_nonexistent_supplier_returns_404(): void
    {
        $this->skipIfSqlite();

        $this->getJson('/api/v1/suppliers/999999', $this->headers())
            ->assertStatus(404);
    }

    public function test_supplier_show_filters_categories_by_company(): void
    {
        $this->skipIfSqlite();
        $otherCompany = Company::create([
            'name'      => 'Empresa Isolada',
            'cnpj'      => '44555666000199',
            'slug'      => 'empresa-isolada',
            'plan'      => 'starter',
            'is_active' => true,
        ]);

        $otherSupplier = Supplier::create([
            'company_id' => $otherCompany->id,
            'name'       => 'Fornecedor Isolado',
            'cnpj'       => '55667788000100',
            'state'      => 'MG',
            'region'     => 'Sudeste',
        ]);

        $otherInvoice = Invoice::create([
            'company_id'     => $otherCompany->id,
            'supplier_id'    => $otherSupplier->id,
            'invoice_number' => 'NF-ISOLATED-001',
            'issue_date'     => now()->subMonth()->format('Y-m-d'),
            'total_value'    => 2000.00,
            'freight_value'  => 100.00,
            'tax_value'      => 160.00,
        ]);

        InvoiceItem::create([
            'invoice_id'  => $otherInvoice->id,
            'product_description' => 'Produto Isolado',
            'category'    => 'Químicos',
            'quantity'    => 50,
            'unit_price'  => 40.00,
            'total_price' => 2000.00,
        ]);

        // Acessa o fornecedor da outra empresa usando credenciais da empresa atual
        $response = $this->getJson("/api/v1/suppliers/{$otherSupplier->id}", $this->headers());

        // O supplier existe → 200, mas as categories/monthly_evolution devem estar vazias
        // porque as invoices são filtradas por company_id da empresa atual
        $response->assertStatus(200);
        $this->assertEmpty($response->json('categories'));
        $this->assertEmpty($response->json('monthly_evolution'));
    }
}
