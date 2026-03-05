<?php

namespace App\DataLumen\Controllers;

use App\DataCore\Controllers\Controller;
use App\DataCore\Models\Invoice;
use App\DataCore\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Fornecedores
 *
 * Análise e ranking de fornecedores com score de classificação.
 */
class SupplierAnalysisController extends Controller
{
    /**
     * Ranking de fornecedores
     *
     * Retorna todos os fornecedores com métricas de compra, score e classificação (Estratégico/Alternativo/Risco).
     *
     * @queryParam start_date string Data inicial (YYYY-MM-DD). Example: 2026-01-01
     * @queryParam end_date string Data final (YYYY-MM-DD). Example: 2026-12-31
     *
     * @response 200 {"data":[{"supplier_id":1,"supplier_name":"Fornecedor Exemplo","cnpj":"12345678000190","region":"Sul","state":"PR","invoice_count":10,"total_purchased":50000.0,"avg_invoice_value":5000.0,"total_freight":4000.0,"avg_freight_pct":8.0,"category_count":3,"first_purchase":"2025-06-01","last_purchase":"2026-01-15","score":75,"classification":"Estratégico"}]}
     */
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('invoices as i')
            ->join('suppliers as s', 's.id', '=', 'i.supplier_id')
            ->join('invoice_items as ii', 'ii.invoice_id', '=', 'i.id')
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->select([
                's.id as supplier_id',
                's.name as supplier_name',
                's.cnpj',
                's.region',
                's.state',
                DB::raw('COUNT(DISTINCT i.id) as invoice_count'),
                DB::raw('SUM(i.total_value) as total_purchased'),
                DB::raw('AVG(i.total_value) as avg_invoice_value'),
                DB::raw('SUM(i.freight_value) as total_freight'),
                DB::raw('SUM(i.tax_value) as total_tax'),
                DB::raw('AVG(i.freight_value / i.total_value * 100) as avg_freight_percent'),
                DB::raw('MIN(i.issue_date) as first_purchase'),
                DB::raw('MAX(i.issue_date) as last_purchase'),
                DB::raw('COUNT(DISTINCT ii.category) as category_count'),
                DB::raw('SUM(ii.total_price) as items_total'),
            ])
            ->groupBy('s.id', 's.name', 's.cnpj', 's.region', 's.state');

        if ($request->filled('start_date')) {
            $query->where('i.issue_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('i.issue_date', '<=', $request->end_date);
        }

        $suppliers = $query->orderByDesc('total_purchased')->get();

        $maxPurchased = $suppliers->max('total_purchased') ?: 1;

        $result = $suppliers->map(function ($s) use ($maxPurchased) {
            $freightPct     = (float) $s->avg_freight_percent;
            $volumeScore    = round(($s->total_purchased / $maxPurchased) * 40);
            $freightScore   = round(max(0, 40 - ($freightPct * 2)));
            $consistScore   = min(20, (int) $s->invoice_count * 2);
            $totalScore     = $volumeScore + $freightScore + $consistScore;

            $classification = match (true) {
                $totalScore >= 70 => 'Estratégico',
                $totalScore >= 40 => 'Alternativo',
                default           => 'Risco',
            };

            return [
                'supplier_id'       => $s->supplier_id,
                'supplier_name'     => $s->supplier_name,
                'cnpj'              => $s->cnpj,
                'region'            => $s->region,
                'state'             => $s->state,
                'invoice_count'     => (int) $s->invoice_count,
                'total_purchased'   => round((float) $s->total_purchased, 2),
                'avg_invoice_value' => round((float) $s->avg_invoice_value, 2),
                'total_freight'     => round((float) $s->total_freight, 2),
                'avg_freight_pct'   => round($freightPct, 2),
                'category_count'    => (int) $s->category_count,
                'first_purchase'    => $s->first_purchase,
                'last_purchase'     => $s->last_purchase,
                'score'             => $totalScore,
                'classification'    => $classification,
            ];
        });

        return response()->json(['data' => $result]);
    }

    /**
     * Detalhe do fornecedor
     *
     * Retorna categorias fornecidas, estatísticas de preço e evolução mensal de compras.
     *
     * @urlParam id integer required ID do fornecedor. Example: 1
     *
     * @response 200 {"supplier":{"id":1,"name":"Fornecedor Exemplo","cnpj":"12345678000190","region":"Sul","state":"PR"},"categories":[{"category":"MDF","item_count":15,"total_value":45000.0,"avg_unit_price":45.5,"min_unit_price":38.0,"max_unit_price":58.0}],"monthly_evolution":[{"month":"2026-01","total_value":5000.0,"invoice_count":2}]}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        $categories = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->where('i.supplier_id', $id)
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->select([
                'ii.category',
                DB::raw('COUNT(*) as item_count'),
                DB::raw('SUM(ii.total_price) as total_value'),
                DB::raw('AVG(ii.unit_price) as avg_unit_price'),
                DB::raw('MIN(ii.unit_price) as min_unit_price'),
                DB::raw('MAX(ii.unit_price) as max_unit_price'),
            ])
            ->groupBy('ii.category')
            ->orderByDesc('total_value')
            ->get();

        $monthlyEvolution = DB::table('invoices as i')
            ->where('i.supplier_id', $id)
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->select([
                DB::raw("DATE_FORMAT(i.issue_date, '%Y-%m') as month"),
                DB::raw('SUM(i.total_value) as total_value'),
                DB::raw('COUNT(*) as invoice_count'),
            ])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'supplier' => [
                'id'     => $supplier->id,
                'name'   => $supplier->name,
                'cnpj'   => $supplier->cnpj,
                'region' => $supplier->region,
                'state'  => $supplier->state,
            ],
            'categories'         => $categories,
            'monthly_evolution'  => $monthlyEvolution,
        ]);
    }
}
