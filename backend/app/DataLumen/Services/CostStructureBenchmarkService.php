<?php

namespace App\DataLumen\Services;

use App\DataCore\Models\CategoryMaster;
use App\DataCore\Models\Company;
use App\DataCore\Repositories\CompanyCostStructureRepository;
use App\DataCore\Repositories\MarketBenchmarkRepository;
use Illuminate\Support\Collection;

class CostStructureBenchmarkService
{
    /** Tolerância para considerar "alinhado ao mercado" (±%) */
    private const DELTA_TOLERANCE = 2.0;

    public function __construct(
        private CompanyCostStructureRepository $costRepo,
        private MarketBenchmarkRepository $benchmarkRepo
    ) {}

    /**
     * Retorna resposta completa de benchmark: meta, categories, summary.
     *
     * Formato:
     * {
     *   "meta": { ... },
     *   "categories": [ { company_percentage, benchmark_percentage, delta, ... } ],
     *   "summary": { total_potential_saving, worst_category, ... }
     * }
     */
    public function getCompanyBenchmark(int $companyId, string $period, ?string $region = null): array
    {
        $company = Company::findOrFail($companyId);
        $effectiveRegion = $region ?? $company->region ?? $company->state ?? 'Nacional';

        // 1. Estrutura de custo da empresa
        $costStructures = $this->costRepo->getForCompanyPeriod($companyId, $period);

        if ($costStructures->isEmpty()) {
            return $this->emptyResponse($company, $period, $effectiveRegion);
        }

        // 2. Benchmark de mercado — tenta região da empresa, depois Nacional
        $benchmarks = $this->benchmarkRepo->getLatestBenchmarks($period, $effectiveRegion);

        if ($benchmarks->isEmpty() && $effectiveRegion !== 'Nacional') {
            $benchmarks = $this->benchmarkRepo->getLatestBenchmarks($period, 'Nacional');
            $effectiveRegion = 'Nacional';
        }

        // Indexar benchmarks por category_master_id
        $benchmarkMap = $benchmarks->keyBy('category_master_id');

        // 3. Todas as categorias ativas
        $categories = CategoryMaster::active()->ordered()->get()->keyBy('id');

        // 4. Total geral da empresa (usa o primeiro registro, é o mesmo para todos)
        $totalCompanySpend = $costStructures->first()->total_company_spend ?? 0;

        // 5. Benchmark sample size e validade
        $benchmarkSampleSize = $benchmarks->max('sample_size') ?? 0;
        $benchmarkIsValid    = $benchmarks->where('is_valid', true)->isNotEmpty();

        // 6. Montar array de categorias com merge
        $categoriesResult = [];
        $totalPotentialSaving = 0;
        $aboveMarket = 0;
        $belowMarket = 0;
        $aligned     = 0;
        $worstCategory = null;

        foreach ($costStructures as $cs) {
            $cat = $categories->get($cs->category_master_id);
            if (!$cat) {
                continue;
            }

            $benchmark = $benchmarkMap->get($cs->category_master_id);

            $companyPercentage    = (float) $cs->percentage;
            $benchmarkPercentage  = $benchmark ? (float) $benchmark->avg_percentage : null;
            $benchmarkMedian      = $benchmark ? (float) $benchmark->median_percentage : null;
            $benchmarkP25         = $benchmark ? (float) $benchmark->p25_percentage : null;
            $benchmarkP75         = $benchmark ? (float) $benchmark->p75_percentage : null;

            // Delta = empresa - benchmark (positivo = acima do mercado)
            $deltaPercentage = $benchmarkPercentage !== null
                ? round($companyPercentage - $benchmarkPercentage, 3)
                : null;

            // Status do delta
            $deltaStatus = $this->determineDeltaStatus($deltaPercentage);

            // Impacto financeiro: saving potencial quando acima do mercado
            $financialImpact = 0;
            $financialImpactLabel = 'Sem dados de benchmark';

            if ($deltaPercentage !== null) {
                if ($deltaStatus === 'above_market') {
                    $financialImpact = round($totalCompanySpend * ($deltaPercentage / 100), 2);
                    $financialImpactLabel = 'Potencial economia de R$ ' . number_format($financialImpact, 2, ',', '.');
                    $totalPotentialSaving += $financialImpact;
                } elseif ($deltaStatus === 'below_market') {
                    $financialImpactLabel = 'Abaixo da média de mercado';
                } else {
                    $financialImpactLabel = 'Alinhado ao mercado';
                }
            }

            $categoryData = [
                'category_slug'         => $cat->slug,
                'category_name'         => $cat->name,
                'company_spend'         => round((float) $cs->total_spend, 2),
                'company_percentage'    => $companyPercentage,
                'benchmark_percentage'  => $benchmarkPercentage,
                'benchmark_median'      => $benchmarkMedian,
                'benchmark_p25'         => $benchmarkP25,
                'benchmark_p75'         => $benchmarkP75,
                'delta_percentage'      => $deltaPercentage,
                'delta_status'          => $deltaStatus,
                'financial_impact'      => $financialImpact,
                'financial_impact_label' => $financialImpactLabel,
            ];

            $categoriesResult[] = $categoryData;

            // Contagem de status
            match ($deltaStatus) {
                'above_market' => $aboveMarket++,
                'below_market' => $belowMarket++,
                'aligned'      => $aligned++,
                default        => null,
            };

            // Pior categoria (maior delta positivo = mais acima do mercado)
            if ($deltaPercentage !== null && $deltaPercentage > 0) {
                if ($worstCategory === null || $financialImpact > $worstCategory['impact']) {
                    $worstCategory = [
                        'slug'   => $cat->slug,
                        'name'   => $cat->name,
                        'delta'  => $deltaPercentage,
                        'impact' => $financialImpact,
                    ];
                }
            }
        }

        // 7. Montar resposta
        return [
            'meta' => [
                'company_id'            => $companyId,
                'company_name'          => $company->name,
                'period'                => $period,
                'period_label'          => $this->periodLabel($period),
                'region'                => $effectiveRegion,
                'benchmark_region'      => $effectiveRegion,
                'benchmark_sample_size' => $benchmarkSampleSize,
                'benchmark_is_valid'    => $benchmarkIsValid,
                'total_company_spend'   => round($totalCompanySpend, 2),
                'calculated_at'         => $costStructures->max('calculated_at'),
            ],
            'categories' => $categoriesResult,
            'summary' => [
                'total_potential_saving'  => round($totalPotentialSaving, 2),
                'categories_above_market' => $aboveMarket,
                'categories_below_market' => $belowMarket,
                'categories_aligned'      => $aligned,
                'worst_category'          => $worstCategory,
            ],
        ];
    }

    /**
     * Retorna períodos disponíveis para o dropdown.
     */
    public function getAvailablePeriods(int $companyId): Collection
    {
        return $this->costRepo->getAvailablePeriods($companyId);
    }

    /**
     * Retorna os períodos que possuem dados de empresa E benchmark de mercado.
     * Usado pelo controller para determinar o melhor período default.
     */
    public function getPeriodsWithBenchmark(int $companyId): Collection
    {
        $companyPeriods  = $this->costRepo->getAvailablePeriods($companyId);
        $benchPeriods    = $this->benchmarkRepo->getAvailablePeriods();

        return $companyPeriods->intersect($benchPeriods)->values();
    }

    /**
     * Determina status do delta com base na tolerância.
     *   delta > +tolerance  → above_market
     *   delta < -tolerance  → below_market
     *   |delta| <= tolerance → aligned
     */
    private function determineDeltaStatus(?float $delta): string
    {
        if ($delta === null) {
            return 'no_data';
        }

        if ($delta > self::DELTA_TOLERANCE) {
            return 'above_market';
        }

        if ($delta < -self::DELTA_TOLERANCE) {
            return 'below_market';
        }

        return 'aligned';
    }

    /**
     * Label amigável para o período "2026-02" → "Fevereiro 2026".
     */
    private function periodLabel(string $period): string
    {
        $months = [
            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
            '04' => 'Abril',   '05' => 'Maio',      '06' => 'Junho',
            '07' => 'Julho',   '08' => 'Agosto',     '09' => 'Setembro',
            '10' => 'Outubro', '11' => 'Novembro',   '12' => 'Dezembro',
        ];

        [$year, $month] = explode('-', $period);

        return ($months[$month] ?? $month) . ' ' . $year;
    }

    /**
     * Retorna os produtos (invoice items) de uma categoria para uma empresa e período.
     * Usado pelo modal de detalhamento por categoria.
     */
    public function getCategoryProducts(int $companyId, string $categorySlug, string $period): array
    {
        $category = CategoryMaster::where('slug', $categorySlug)->firstOrFail();

        // Buscar todos os invoice items da categoria no período
        $items = \DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->join('suppliers as sup', 'inv.supplier_id', '=', 'sup.id')
            ->where('inv.company_id', $companyId)
            ->where('ii.category_master_id', $category->id)
            ->whereRaw("DATE_FORMAT(inv.issue_date, '%Y-%m') = ?", [$period])
            ->select(
                'ii.id',
                'ii.product_description',
                'ii.quantity',
                'ii.unit_price',
                'ii.total_price',
                'inv.invoice_number',
                'inv.issue_date',
                'inv.freight_value',
                'inv.tax_value',
                'inv.total_value as invoice_total',
                'sup.name as supplier_name',
                'sup.cnpj as supplier_cnpj'
            )
            ->orderByDesc('ii.total_price')
            ->get();

        // Totais da categoria
        $totalCategorySpend = $items->sum('total_price');

        // Gasto total da empresa no período (para calcular % empresa)
        $totalCompanySpend = $this->costRepo->getTotalCompanySpend($companyId, $period);

        // Benchmark da categoria
        $company = Company::findOrFail($companyId);
        $effectiveRegion = $company->region ?? $company->state ?? 'Nacional';
        $benchmarks = $this->benchmarkRepo->getLatestBenchmarks($period, $effectiveRegion);
        if ($benchmarks->isEmpty() && $effectiveRegion !== 'Nacional') {
            $benchmarks = $this->benchmarkRepo->getLatestBenchmarks($period, 'Nacional');
        }
        $benchmark = $benchmarks->firstWhere('category_master_id', $category->id);

        $benchmarkPercentage = $benchmark ? (float) $benchmark->avg_percentage : null;
        $companyPercentage = $totalCompanySpend > 0
            ? round(($totalCategorySpend / $totalCompanySpend) * 100, 3)
            : 0;

        $deltaPercentage = $benchmarkPercentage !== null
            ? round($companyPercentage - $benchmarkPercentage, 3)
            : null;

        $deltaStatus = $this->determineDeltaStatus($deltaPercentage);

        // Construir lista de produtos com % individual
        $products = $items->map(function ($item) use ($totalCategorySpend, $totalCompanySpend) {
            $pctCategory = $totalCategorySpend > 0
                ? round(($item->total_price / $totalCategorySpend) * 100, 2)
                : 0;
            $pctCompany = $totalCompanySpend > 0
                ? round(($item->total_price / $totalCompanySpend) * 100, 3)
                : 0;

            // Frete e imposto rateados proporcionalmente
            $freightAllocated = $item->invoice_total > 0
                ? round($item->freight_value * ($item->total_price / $item->invoice_total), 2)
                : 0;
            $taxAllocated = $item->invoice_total > 0
                ? round($item->tax_value * ($item->total_price / $item->invoice_total), 2)
                : 0;

            return [
                'id'                  => $item->id,
                'product_description' => $item->product_description,
                'quantity'            => (float) $item->quantity,
                'unit_price'          => (float) $item->unit_price,
                'total_price'         => (float) $item->total_price,
                'pct_of_category'     => $pctCategory,
                'pct_of_company'      => $pctCompany,
                'supplier_name'       => $item->supplier_name,
                'supplier_cnpj'       => $item->supplier_cnpj,
                'invoice_number'      => $item->invoice_number,
                'issue_date'          => $item->issue_date,
                'freight_allocated'   => $freightAllocated,
                'tax_allocated'       => $taxAllocated,
                'total_with_costs'    => round($item->total_price + $freightAllocated + $taxAllocated, 2),
            ];
        })->values()->toArray();

        return [
            'category' => [
                'slug'                 => $category->slug,
                'name'                 => $category->name,
                'total_spend'          => round($totalCategorySpend, 2),
                'company_percentage'   => $companyPercentage,
                'benchmark_percentage' => $benchmarkPercentage,
                'delta_percentage'     => $deltaPercentage,
                'delta_status'         => $deltaStatus,
            ],
            'meta' => [
                'period'             => $period,
                'total_company_spend' => round($totalCompanySpend, 2),
                'products_count'     => count($products),
                'unique_suppliers'   => collect($products)->pluck('supplier_name')->unique()->count(),
            ],
            'products' => $products,
        ];
    }

    /**
     * Resposta vazia quando não há dados.
     */
    private function emptyResponse(Company $company, string $period, string $region): array
    {
        return [
            'meta' => [
                'company_id'            => $company->id,
                'company_name'          => $company->name,
                'period'                => $period,
                'period_label'          => $this->periodLabel($period),
                'region'                => $region,
                'benchmark_region'      => $region,
                'benchmark_sample_size' => 0,
                'benchmark_is_valid'    => false,
                'total_company_spend'   => 0,
                'calculated_at'         => null,
            ],
            'categories' => [],
            'summary' => [
                'total_potential_saving'  => 0,
                'categories_above_market' => 0,
                'categories_below_market' => 0,
                'categories_aligned'      => 0,
                'worst_category'          => null,
            ],
        ];
    }
}
