<?php

namespace App\DataLumen\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataLumen\Services\BenchmarkService;
use App\DataForge\Services\CategoryRankingService;
use App\DataLumen\Services\FreightImpactService;
use App\DataLumen\Services\PriceDispersionService;
use App\DataLumen\Services\PriceEvolutionService;
use App\DataLumen\Services\TcoBreakdownService;
use App\DataLumen\Services\TcoCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Dashboard & Análises
 *
 * Endpoints de análise: KPIs, TCO, evolução de preços, dispersão, frete e ranking de categorias.
 * Todos aceitam filtros opcionais de período (start_date, end_date).
 */
class DashboardController extends Controller
{
    public function __construct(
        private TcoCalculatorService   $tcoService,
        private BenchmarkService       $benchmarkService,
        private PriceEvolutionService  $priceEvolutionService,
        private PriceDispersionService $dispersionService,
        private FreightImpactService   $freightService,
        private CategoryRankingService $rankingService,
        private TcoBreakdownService    $tcoBreakdownService,
    ) {}

    /**
     * KPIs principais
     *
     * Retorna indicadores-chave: TCO médio, variação vs mercado, peso do frete, categoria top e benchmark por categoria.
     *
     * @queryParam start_date string Data inicial (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam end_date string Data final (YYYY-MM-DD). Example: 2026-12-31
     *
     * @response 200 {"overall_tco_average":52.3,"market_variation_percent":-3.5,"freight_weight_percent":8.2,"top_category":{"category":"MDF","total_value":45000.0},"benchmark_by_category":[{"category":"MDF","company_tco_avg":48.5,"market_tco_avg":50.2,"variation_percent":-3.4,"status":"below_market"}]}
     */
    public function kpis(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $tcoByCategory  = $this->tcoService->tcoAverageByCategory($startDate, $endDate);
        $benchmarkData  = $this->benchmarkService->compareWithMarket($tcoByCategory);
        $overallTco     = $this->tcoService->overallTcoAverage($startDate, $endDate);
        $overallVariation = $this->benchmarkService->overallVariation($benchmarkData);
        $freightPercent  = $this->freightService->overallFreightPercent($startDate, $endDate);
        $topCat          = $this->rankingService->topCategory($startDate, $endDate);

        return response()->json([
            'overall_tco_average'    => $overallTco,
            'market_variation_percent' => $overallVariation,
            'freight_weight_percent' => $freightPercent,
            'top_category'           => $topCat,
            'benchmark_by_category'  => $benchmarkData->values(),
        ]);
    }

    /**
     * Composição do TCO
     *
     * Retorna breakdown do TCO (preço unitário + frete + imposto) por categoria, empresa vs mercado.
     *
     * @queryParam start_date string Data inicial (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam end_date string Data final (YYYY-MM-DD). Example: 2026-12-31
     *
     * @response 200 {"company":[{"category":"MDF","avg_unit_price":45.5,"avg_freight":3.2,"avg_tax":4.1,"tco_total":52.8}],"market":[{"category":"MDF","avg_unit_price":47.0,"avg_freight":3.5,"avg_tax":4.3,"tco_total":54.8}]}
     */
    public function tcoBreakdown(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $company = $this->tcoBreakdownService->breakdownByCategory($startDate, $endDate);
        $market  = $this->benchmarkService->marketTcoBreakdown($company);

        return response()->json([
            'company' => $company->values(),
            'market'  => $market->values(),
        ]);
    }

    /**
     * Custo bruto vs líquido
     *
     * Retorna custo bruto, crédito tributário e custo líquido por categoria.
     *
     * @queryParam start_date string Data inicial (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam end_date string Data final (YYYY-MM-DD). Example: 2026-12-31
     *
     * @response 200 {"data":[{"category":"MDF","gross_cost":45000.0,"tax_credit":3825.0,"net_cost":41175.0,"credit_percent":8.5}]}
     */
    public function grossVsNet(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $rows = DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->when($startDate, fn($q) => $q->where('inv.issue_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('inv.issue_date', '<=', $endDate))
            ->when(app()->has('current_company_id'), fn($q) => $q->where('inv.company_id', app('current_company_id')))
            ->select(
                'ii.category',
                DB::raw('SUM(ii.total_price) as gross_cost'),
                DB::raw('SUM(inv.tax_value * (ii.total_price / inv.total_value)) as tax_credit')
            )
            ->groupBy('ii.category')
            ->orderBy('ii.category')
            ->get();

        $data = $rows->map(fn($row) => [
            'category'       => $row->category,
            'gross_cost'     => round((float) $row->gross_cost, 2),
            'tax_credit'     => round((float) $row->tax_credit, 2),
            'net_cost'       => round((float) $row->gross_cost - (float) $row->tax_credit, 2),
            'credit_percent' => (float) $row->gross_cost > 0
                ? round(((float) $row->tax_credit / (float) $row->gross_cost) * 100, 1)
                : 0,
        ]);

        return response()->json(['data' => $data->values()]);
    }

    /**
     * Evolução de preços
     *
     * Retorna evolução mensal de preços médios por categoria, incluindo curva de mercado.
     *
     * @queryParam category string Filtrar por categoria específica. Example: MDF
     *
     * @response 200 {"data":[{"month":"2026-01","MDF":45.5,"Mercado MDF":47.0}],"categories":["MDF","Ferragens"],"market_categories":["Mercado MDF","Mercado Ferragens"]}
     */
    public function priceEvolution(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $categories = $this->priceEvolutionService->availableCategories();

        $data = $this->priceEvolutionService->formatForChart($category, true);

        $marketCategories = array_map(fn($c) => "Mercado {$c}", $categories);

        return response()->json([
            'data'              => $data,
            'categories'        => $categories,
            'market_categories' => $marketCategories,
        ]);
    }

    /**
     * Índice de preços
     *
     * Retorna índice percentual por período (base 100) para análise de sazonalidade.
     *
     * @queryParam category string Filtrar por categoria específica. Example: MDF
     *
     * @response 200 {"data":[{"month":"2026-01","MDF":100,"Ferragens":100}],"categories":["MDF","Ferragens"]}
     */
    public function priceIndex(Request $request): JsonResponse
    {
        $category = $request->query('category');

        return response()->json([
            'data'       => $this->priceEvolutionService->priceIndex($category),
            'categories' => $this->priceEvolutionService->availableCategories(),
        ]);
    }

    /**
     * Dispersão de preços
     *
     * Retorna preço mínimo, médio e máximo por categoria, indicando variabilidade.
     *
     * @queryParam start_date string Data inicial (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam end_date string Data final (YYYY-MM-DD). Example: 2026-12-31
     *
     * @response 200 {"data":[{"category":"MDF","min_price":38.0,"avg_price":45.5,"max_price":58.0,"range":20.0}]}
     */
    public function dispersion(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        return response()->json([
            'data' => $this->dispersionService->dispersionByCategory($startDate, $endDate)->values(),
        ]);
    }

    /**
     * Impacto do frete
     *
     * Retorna o peso percentual do frete no custo total por categoria.
     *
     * @queryParam start_date string Data inicial (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam end_date string Data final (YYYY-MM-DD). Example: 2026-12-31
     *
     * @response 200 {"data":[{"category":"MDF","total_invoiced":45000.0,"total_freight":3600.0,"freight_percent":8.0}],"overall_percent":7.5}
     */
    public function freightImpact(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        return response()->json([
            'data'            => $this->freightService->freightImpactByCategory($startDate, $endDate)->values(),
            'overall_percent' => $this->freightService->overallFreightPercent($startDate, $endDate),
        ]);
    }

    /**
     * Ranking de categorias
     *
     * Retorna categorias ordenadas por valor total investido.
     *
     * @queryParam start_date string Data inicial (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam end_date string Data final (YYYY-MM-DD). Example: 2026-12-31
     *
     * @response 200 {"data":[{"rank":1,"category":"MDF","total_value":45000.0,"total_qty":1000,"items_count":15}]}
     */
    public function categoryRanking(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        return response()->json([
            'data' => $this->rankingService->rankingByValue($startDate, $endDate)->values(),
        ]);
    }
}
