<?php

namespace App\DataLumen\Controllers;

use App\DataCore\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group Saving & Cost Avoidance
 *
 * Análise de economia realizada e custos evitados por categoria.
 * Compara preço médio do período atual (3 meses) vs anterior (3 meses).
 */
class SavingController extends Controller
{
    /**
     * Saving por categoria
     *
     * Retorna saving real (variação de preço) e cost avoidance (vs índice de mercado) por categoria.
     *
     * @response 200 {"total_saving":1250.0,"total_cost_avoid":3800.0,"period_label":"Últimos 3 meses vs. 3 meses anteriores","categories":[{"category":"MDF","prev_avg_price":48.0,"curr_avg_price":45.5,"price_change_pct":5.21,"saving_abs":250.0,"cost_avoid_abs":800.0,"market_index_pct":8.2,"hypothetical_avg":51.94,"total_spend":45000.0,"status":"saving"}]}
     */
    public function index(): JsonResponse
    {
        $marketIndexPct = [
            'MDF'        => 8.2,
            'Ferragens'  => 5.5,
            'Químicos'   => 11.3,
            'Aramados'   => 6.8,
            'Embalagens' => 4.9,
            'Acessórios' => 3.7,
        ];

        $currentPeriodStart  = now()->subMonths(3)->format('Y-m-d');
        $previousPeriodStart = now()->subMonths(6)->format('Y-m-d');
        $previousPeriodEnd   = now()->subMonths(3)->format('Y-m-d');

        $current = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->select([
                'ii.category',
                DB::raw('AVG(ii.unit_price) as avg_price'),
                DB::raw('SUM(ii.total_price) as total_spend'),
                DB::raw('SUM(ii.quantity) as total_qty'),
            ])
            ->where('i.issue_date', '>=', $currentPeriodStart)
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->groupBy('ii.category')
            ->get()
            ->keyBy('category');

        $previous = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->select([
                'ii.category',
                DB::raw('AVG(ii.unit_price) as avg_price'),
                DB::raw('SUM(ii.total_price) as total_spend'),
            ])
            ->where('i.issue_date', '>=', $previousPeriodStart)
            ->where('i.issue_date', '<',  $previousPeriodEnd)
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->groupBy('ii.category')
            ->get()
            ->keyBy('category');

        $categories   = [];
        $totalSaving  = 0.0;
        $totalAvoid   = 0.0;

        foreach ($current as $cat => $curr) {
            $prev        = $previous[$cat] ?? null;
            $prevAvg     = $prev ? (float) $prev->avg_price : null;
            $currAvg     = (float) $curr->avg_price;
            $totalSpend  = (float) $curr->total_spend;
            $totalQty    = (float) $curr->total_qty;
            $marketIdx   = $marketIndexPct[$cat] ?? 6.0;

            $savingPct   = $prevAvg ? round((($prevAvg - $currAvg) / $prevAvg) * 100, 2) : 0;
            $savingAbs   = $prevAvg ? round(($prevAvg - $currAvg) * $totalQty, 2) : 0;

            $hypotheticalAvg = $prevAvg ? round($prevAvg * (1 + $marketIdx / 100), 2) : null;
            $costAvoidAbs    = ($hypotheticalAvg && $hypotheticalAvg > $currAvg)
                ? round(($hypotheticalAvg - $currAvg) * $totalQty, 2)
                : 0;

            $totalSaving += $savingAbs;
            $totalAvoid  += $costAvoidAbs;

            $categories[] = [
                'category'          => $cat,
                'prev_avg_price'    => $prevAvg ? round($prevAvg, 2) : null,
                'curr_avg_price'    => round($currAvg, 2),
                'price_change_pct'  => $savingPct,
                'saving_abs'        => $savingAbs,
                'cost_avoid_abs'    => $costAvoidAbs,
                'market_index_pct'  => $marketIdx,
                'hypothetical_avg'  => $hypotheticalAvg,
                'total_spend'       => round($totalSpend, 2),
                'status'            => $savingPct > 0 ? 'saving' : ($savingPct < -2 ? 'overpaying' : 'stable'),
            ];
        }

        usort($categories, fn($a, $b) => $b['cost_avoid_abs'] - $a['cost_avoid_abs']);

        return response()->json([
            'total_saving'     => round($totalSaving, 2),
            'total_cost_avoid' => round($totalAvoid, 2),
            'period_label'     => 'Últimos 3 meses vs. 3 meses anteriores',
            'categories'       => $categories,
        ]);
    }
}
