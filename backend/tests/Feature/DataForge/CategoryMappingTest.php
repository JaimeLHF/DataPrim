<?php

namespace Tests\Feature\DataForge;

use App\DataCore\Models\CategoryMaster;
use App\DataCore\Models\Company;
use App\DataCore\Models\Invoice;
use App\DataCore\Models\InvoiceItem;
use App\DataCore\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes do comando benchmark:map-categories (DataForge).
 *
 * O comando lê invoice_items.category (string livre) e preenche
 * invoice_items.category_master_id de acordo com a taxonomia canônica
 * definida nos category_masters.
 *
 * Cobre:
 * - Comando executa com sucesso quando há CategoryMasters seedados
 * - Itens sem category_master_id são mapeados corretamente
 * - Itens já mapeados não são remapeados (sem --force)
 * - Mensagem de erro quando não há CategoryMasters
 */
class CategoryMappingTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    /**
     * Slugs necessários (conforme $categoryMap em MapCategories.php):
     *   MDF → chapa | Ferragens → ferragem | Químicos → quimico
     *   Aramados → aramado | Embalagens → embalagem | Acessórios → acessorio | Outros → outros
     */
    private array $categoryMasterSlugs = [
        'chapa'     => 'MDF',
        'ferragem'  => 'Ferragens',
        'quimico'   => 'Químicos',
        'aramado'   => 'Aramados',
        'embalagem' => 'Embalagens',
        'acessorio' => 'Acessórios',
        'outros'    => 'Outros',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'      => 'Empresa Categoria Teste',
            'cnpj'      => '66777888000133',
            'slug'      => 'empresa-categoria-teste',
            'plan'      => 'starter',
            'is_active' => true,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function seedCategoryMasters(): array
    {
        $masters = [];
        $sort    = 1;
        foreach ($this->categoryMasterSlugs as $slug => $name) {
            $masters[$slug] = CategoryMaster::create([
                'slug'      => $slug,
                'name'      => $name,
                'group'     => 'materia-prima',
                'is_active' => true,
                'sort_order' => $sort++,
            ]);
        }
        return $masters;
    }

    private function makeInvoiceItem(string $category, ?int $categoryMasterId = null): InvoiceItem
    {
        $supplier = Supplier::firstOrCreate(
            ['cnpj' => '11111111000111', 'company_id' => $this->company->id],
            ['name' => 'Fornecedor Teste', 'state' => 'PR', 'region' => 'Sul']
        );

        $invoice = Invoice::create([
            'company_id'     => $this->company->id,
            'supplier_id'    => $supplier->id,
            'invoice_number' => 'NF-CAT-' . uniqid(),
            'issue_date'     => now()->format('Y-m-d'),
            'total_value'    => 1000.00,
            'freight_value'  => 50.00,
            'tax_value'      => 80.00,
        ]);

        return InvoiceItem::create([
            'invoice_id'         => $invoice->id,
            'product_description' => $category . ' teste',
            'category'           => $category,
            'quantity'           => 10,
            'unit_price'         => 100.00,
            'total_price'        => 1000.00,
            'category_master_id' => $categoryMasterId,
        ]);
    }

    // ── Testes ────────────────────────────────────────────────────────────────

    public function test_map_categories_command_fails_when_no_category_masters(): void
    {
        // Sem CategoryMaster criados → comando deve falhar com mensagem de erro
        $this->artisan('benchmark:map-categories')
            ->assertExitCode(1);
    }

    public function test_map_categories_command_succeeds_with_category_masters(): void
    {
        $this->seedCategoryMasters();

        $this->artisan('benchmark:map-categories')
            ->assertExitCode(0);
    }

    public function test_map_categories_maps_unmapped_items(): void
    {
        $masters = $this->seedCategoryMasters();

        // Cria um item sem category_master_id
        $item = $this->makeInvoiceItem('MDF', null);
        $this->assertNull($item->category_master_id);

        $this->artisan('benchmark:map-categories')
            ->assertExitCode(0);

        // Após o comando, o item deve ter category_master_id preenchido
        $item->refresh();
        $this->assertNotNull($item->category_master_id);
        $this->assertEquals($masters['chapa']->id, $item->category_master_id);
    }

    public function test_map_categories_does_not_remap_already_mapped_items(): void
    {
        $masters = $this->seedCategoryMasters();

        // Cria um item JÁ mapeado para 'ferragem'
        $item = $this->makeInvoiceItem('MDF', $masters['ferragem']->id);
        $this->assertEquals($masters['ferragem']->id, $item->category_master_id);

        // Roda sem --force → itens já mapeados não devem ser alterados
        $this->artisan('benchmark:map-categories')
            ->assertExitCode(0);

        $item->refresh();
        // O category_master_id não deve ter sido alterado (ainda é 'ferragem')
        $this->assertEquals($masters['ferragem']->id, $item->category_master_id);
    }

    public function test_map_categories_force_remaps_all_items(): void
    {
        $masters = $this->seedCategoryMasters();

        // Cria item MDF mapeado incorretamente para 'ferragem'
        $item = $this->makeInvoiceItem('MDF', $masters['ferragem']->id);

        // Com --force, deve remapear corretamente para 'chapa'
        $this->artisan('benchmark:map-categories --force')
            ->assertExitCode(0);

        $item->refresh();
        $this->assertEquals($masters['chapa']->id, $item->category_master_id);
    }

    public function test_map_categories_dry_run_does_not_persist(): void
    {
        $this->seedCategoryMasters();

        $item = $this->makeInvoiceItem('MDF', null);

        $this->artisan('benchmark:map-categories --dry-run')
            ->assertExitCode(0);

        // Com --dry-run nenhuma alteração deve ser feita
        $item->refresh();
        $this->assertNull($item->category_master_id);
    }

    public function test_map_categories_correctly_maps_multiple_categories(): void
    {
        $masters = $this->seedCategoryMasters();

        // Cria um item para cada categoria mapeada
        $categoriesToTest = ['MDF' => 'chapa', 'Ferragens' => 'ferragem', 'Químicos' => 'quimico'];

        $items = [];
        foreach ($categoriesToTest as $category => $expectedSlug) {
            $items[$category] = $this->makeInvoiceItem($category, null);
        }

        $this->artisan('benchmark:map-categories')->assertExitCode(0);

        foreach ($categoriesToTest as $category => $expectedSlug) {
            $items[$category]->refresh();
            $this->assertEquals(
                $masters[$expectedSlug]->id,
                $items[$category]->category_master_id,
                "Categoria '{$category}' deveria mapear para slug '{$expectedSlug}'"
            );
        }
    }
}
