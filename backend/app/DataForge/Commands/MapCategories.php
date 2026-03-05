<?php

namespace App\DataForge\Commands;

use App\DataCore\Models\CategoryMaster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MapCategories extends Command
{
    protected $signature = 'benchmark:map-categories
                            {--dry-run : Mostrar mapeamento sem persistir}
                            {--force : Remapear mesmo itens já vinculados}';

    protected $description = 'Vincula invoice_items.category (string) ao category_master_id correspondente';

    /**
     * Mapeamento: valor do campo `category` (string livre) → slug do category_masters.
     *
     * Esse mapa reflete as categorias geradas pelo InvoiceSeeder e pelo
     * NfeXmlImportService::inferCategory(). Adicione novas entradas
     * conforme novas categorias apareçam no sistema.
     */
    private array $categoryMap = [
        // Exato match (case-insensitive)
        'MDF'        => 'chapa',
        'Ferragens'  => 'ferragem',
        'Químicos'   => 'quimico',
        'Aramados'   => 'aramado',
        'Embalagens' => 'embalagem',
        'Acessórios' => 'acessorio',
        'Outros'     => 'outros',
    ];

    public function handle(): int
    {
        $masters = CategoryMaster::pluck('id', 'slug');

        if ($masters->isEmpty()) {
            $this->error('Nenhuma categoria master encontrada. Execute primeiro: php artisan db:seed --class=CategoryMasterSeeder');
            return self::FAILURE;
        }

        // Montar mapa: category string → category_master_id
        $resolvedMap = [];
        foreach ($this->categoryMap as $categoryString => $slug) {
            if ($masters->has($slug)) {
                $resolvedMap[$categoryString] = $masters[$slug];
            } else {
                $this->warn("Slug '{$slug}' não encontrado em category_masters. Ignorando '{$categoryString}'.");
            }
        }

        $this->info('Mapeamento configurado:');
        $this->table(
            ['category (string)', 'slug', 'category_master_id'],
            collect($resolvedMap)->map(fn($id, $cat) => [
                $cat,
                $this->categoryMap[$cat],
                $id,
            ])->values()->toArray()
        );

        // Contar itens a mapear
        $query = DB::table('invoice_items');

        if (!$this->option('force')) {
            $query->whereNull('category_master_id');
        }

        $totalItems = $query->count();
        $this->info("Itens a processar: {$totalItems}");

        if ($totalItems === 0) {
            $this->info('Nenhum item para mapear.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn('Modo --dry-run ativado. Nenhuma alteração será feita.');
            $this->showPreview($resolvedMap);
            return self::SUCCESS;
        }

        // Executar mapeamento em batch
        $updated = 0;
        $unmapped = 0;

        foreach ($resolvedMap as $categoryString => $masterId) {
            $query = DB::table('invoice_items')
                ->where('category', $categoryString);

            if (!$this->option('force')) {
                $query->whereNull('category_master_id');
            }

            $count = $query->update(['category_master_id' => $masterId]);
            $updated += $count;

            if ($count > 0) {
                $this->line("  ✓ '{$categoryString}' → master_id={$masterId}: {$count} itens atualizados");
            }
        }

        // Verificar itens não mapeados
        $unmappedQuery = DB::table('invoice_items')
            ->whereNull('category_master_id');

        $unmapped = $unmappedQuery->count();

        // Mapear itens não reconhecidos para "outros"
        if ($unmapped > 0 && $masters->has('outros')) {
            $outrosId = $masters['outros'];
            $fallbackCount = $unmappedQuery->update(['category_master_id' => $outrosId]);
            $this->warn("  ⚠ {$fallbackCount} itens sem match mapeados para 'outros' (master_id={$outrosId})");
            $updated += $fallbackCount;
            $unmapped -= $fallbackCount;
        }

        $this->newLine();
        $this->info("Mapeamento concluído: {$updated} itens atualizados.");

        if ($unmapped > 0) {
            $this->warn("{$unmapped} itens permaneceram sem category_master_id.");

            // Listar categorias não mapeadas para diagnóstico
            $unmappedCategories = DB::table('invoice_items')
                ->whereNull('category_master_id')
                ->select('category', DB::raw('COUNT(*) as total'))
                ->groupBy('category')
                ->get();

            $this->table(
                ['category (não mapeada)', 'total itens'],
                $unmappedCategories->map(fn($r) => [$r->category, $r->total])->toArray()
            );
        }

        return self::SUCCESS;
    }

    private function showPreview(array $resolvedMap): void
    {
        $preview = [];

        foreach ($resolvedMap as $categoryString => $masterId) {
            $query = DB::table('invoice_items')
                ->where('category', $categoryString);

            if (!$this->option('force')) {
                $query->whereNull('category_master_id');
            }

            $count = $query->count();
            $preview[] = [$categoryString, $masterId, $count];
        }

        // Não mapeados
        $unmapped = DB::table('invoice_items')
            ->whereNull('category_master_id')
            ->whereNotIn('category', array_keys($resolvedMap))
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->get();

        foreach ($unmapped as $row) {
            $preview[] = [$row->category, '→ outros (fallback)', $row->total];
        }

        $this->table(['category', 'category_master_id', 'itens afetados'], $preview);
    }
}
