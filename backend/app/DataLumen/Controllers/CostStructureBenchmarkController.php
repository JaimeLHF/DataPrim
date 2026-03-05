<?php

namespace App\DataLumen\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataLumen\Services\CostStructureBenchmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Benchmark de Estrutura de Custo
 *
 * Comparação da estrutura de custo da empresa com benchmarks de mercado por categoria e período.
 */
class CostStructureBenchmarkController extends Controller
{
    public function __construct(
        private CostStructureBenchmarkService $service
    ) {}

    /**
     * Benchmark por categoria
     *
     * Retorna estrutura de custo da empresa vs mercado por categoria num período específico.
     * Se nenhum período for informado, usa o mais recente com dados disponíveis.
     *
     * @queryParam period string Período no formato YYYY-MM. Example: 2026-01
     * @queryParam region string Filtrar por região. Example: Sul
     *
     * @response 200 {"meta":{"company_id":1,"company_name":"Móveis Ruiz","period":"2026-01","period_label":"Jan/2026","region":"Sul","benchmark_region":"Sul","benchmark_sample_size":15,"benchmark_is_valid":true,"total_company_spend":120000.0,"calculated_at":"2026-01-15T10:00:00Z"},"categories":[{"category_slug":"mdf","category_name":"MDF","company_spend":45000.0,"company_percentage":37.5,"benchmark_percentage":35.0,"benchmark_median":42.0,"benchmark_p25":38.0,"benchmark_p75":48.0,"delta_percentage":2.5,"delta_status":"above_market","financial_impact":3000.0,"financial_impact_label":"R$ 3.000 acima do mercado"}],"summary":{"total_potential_saving":5000.0,"categories_above_market":2,"categories_below_market":3,"categories_aligned":1,"worst_category":{"slug":"mdf","name":"MDF","delta":2.5,"impact":3000.0}}}
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = (int) app('current_company_id');
        $period    = $request->query('period');
        $region    = $request->query('region');

        if (!$period) {
            $periodsWithBench = $this->service->getPeriodsWithBenchmark($companyId);
            $period = $periodsWithBench->last();

            if (!$period) {
                $allPeriods = $this->service->getAvailablePeriods($companyId);
                $period = $allPeriods->last();
            }

            if (!$period) {
                return response()->json([
                    'meta'       => ['company_id' => $companyId, 'error' => 'Nenhum período disponível'],
                    'categories' => [],
                    'summary'    => ['total_potential_saving' => 0],
                ], 200);
            }
        }

        $data = $this->service->getCompanyBenchmark($companyId, $period, $region);

        return response()->json($data);
    }

    /**
     * Produtos por categoria
     *
     * Retorna detalhamento dos produtos comprados em uma categoria específica.
     *
     * @queryParam category string required Slug da categoria. Example: mdf
     * @queryParam period string required Período no formato YYYY-MM. Example: 2026-01
     *
     * @response 200 {"category":{"slug":"mdf","name":"MDF","total_spend":45000.0,"company_percentage":37.5,"benchmark_percentage":35.0,"delta_percentage":2.5,"delta_status":"above_market"},"meta":{"period":"2026-01","total_company_spend":120000.0,"products_count":5,"unique_suppliers":2},"products":[{"id":1,"product_description":"MDF 18mm Branco","quantity":100,"unit_price":45.5,"total_price":4550.0,"pct_of_category":10.1,"pct_of_company":3.8,"supplier_name":"Fornecedor Exemplo","supplier_cnpj":"12345678000190","invoice_number":"NF-001","issue_date":"2026-01-15","freight_allocated":36.0,"tax_allocated":34.0,"total_with_costs":4620.0}]}
     * @response 422 {"error":"Parâmetros \"category\" e \"period\" são obrigatórios."}
     * @response 404 {"error":"Categoria não encontrada."}
     */
    public function categoryProducts(Request $request): JsonResponse
    {
        $companyId    = (int) $request->query('company_id', 1);
        $categorySlug = $request->query('category');
        $period       = $request->query('period');

        if (!$categorySlug || !$period) {
            return response()->json([
                'error' => 'Parâmetros "category" e "period" são obrigatórios.',
            ], 422);
        }

        try {
            $data = $this->service->getCategoryProducts($companyId, $categorySlug, $period);
            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Categoria não encontrada.'], 404);
        }
    }

    /**
     * Períodos disponíveis
     *
     * Retorna lista de períodos (YYYY-MM) para o dropdown de seleção.
     *
     * @response 200 {"periods":[{"value":"2026-01","label":"Jan/2026"},{"value":"2025-12","label":"Dez/2025"}]}
     */
    public function periods(Request $request): JsonResponse
    {
        $companyId = (int) app('current_company_id');

        $periods = $this->service->getAvailablePeriods($companyId);

        return response()->json([
            'periods' => $periods->map(fn (string $p) => [
                'value' => $p,
                'label' => $this->periodLabel($p),
            ])->values(),
        ]);
    }

    private function periodLabel(string $period): string
    {
        $months = [
            '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr',
            '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez',
        ];

        [$year, $month] = explode('-', $period);
        return ($months[$month] ?? $month) . '/' . $year;
    }
}
