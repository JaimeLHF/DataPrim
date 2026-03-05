<?php

namespace App\DataCore\Controllers;

use App\DataCore\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group Alertas
 *
 * Alertas automáticos gerados pela análise de dados de compras.
 * Tipos: anomalia de preço, frete alto, alta consecutiva e concentração de fornecedor.
 */
class AlertController extends Controller
{
    /**
     * Listar alertas
     *
     * Retorna alertas ordenados por severidade (high primeiro) e data.
     *
     * @response 200 {"total":5,"high":2,"medium":3,"alerts":[{"type":"price_anomaly","severity":"high","title":"Preço acima da média — MDF","message":"MDF 18mm Branco comprado por R$ 65,00 (+42.9% acima da média histórica)","detail":"NF-e NF-001 · Fornecedor Exemplo · 15/01/2026","date":"2026-01-15"},{"type":"high_freight","severity":"medium","title":"Frete alto — Ferragens","message":"Impacto médio de 15.2% do frete no custo total desta categoria (8 notas)","detail":"Considere renegociar frete ou buscar fornecedores mais próximos","date":"2026-03-03"}]}
     */
    public function index(): JsonResponse
    {
        $alerts = [];

        // 1. Price anomaly: invoices where unit price > avg + 20%
        $avgByCategory = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->select('ii.category', DB::raw('AVG(ii.unit_price) as avg_price'))
            ->groupBy('ii.category')
            ->pluck('avg_price', 'ii.category');

        $priceAnomalies = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('suppliers as s', 's.id', '=', 'i.supplier_id')
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->select([
                'ii.category',
                'ii.product_description',
                'ii.unit_price',
                'i.invoice_number',
                'i.issue_date',
                's.name as supplier_name',
            ])
            ->orderByDesc('i.issue_date')
            ->get()
            ->filter(function ($row) use ($avgByCategory) {
                $avg = $avgByCategory[$row->category] ?? null;
                if (! $avg) return false;
                return $row->unit_price > ($avg * 1.20);
            })
            ->take(10);

        foreach ($priceAnomalies as $item) {
            $avg       = $avgByCategory[$item->category];
            $deviation = round((($item->unit_price - $avg) / $avg) * 100, 1);
            $alerts[]  = [
                'type'     => 'price_anomaly',
                'severity' => $deviation > 35 ? 'high' : 'medium',
                'title'    => "Preço acima da média — {$item->category}",
                'message'  => "{$item->product_description} comprado por R$ " .
                    number_format($item->unit_price, 2, ',', '.') .
                    " (+{$deviation}% acima da média histórica)",
                'detail'   => "NF-e {$item->invoice_number} · {$item->supplier_name} · " .
                    date('d/m/Y', strtotime($item->issue_date)),
                'date'     => $item->issue_date,
            ];
        }

        // 2. High freight: categories where freight % > 12%
        $highFreight = DB::table('invoices as i')
            ->join('invoice_items as ii', 'ii.invoice_id', '=', 'i.id')
            ->join('suppliers as s', 's.id', '=', 'i.supplier_id')
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->select([
                'ii.category',
                DB::raw('AVG(i.freight_value / i.total_value * 100) as avg_freight_pct'),
                DB::raw('COUNT(DISTINCT i.id) as invoice_count'),
            ])
            ->groupBy('ii.category')
            ->having('avg_freight_pct', '>', 12)
            ->orderByDesc('avg_freight_pct')
            ->get();

        foreach ($highFreight as $row) {
            $pct      = round((float) $row->avg_freight_pct, 1);
            $alerts[] = [
                'type'     => 'high_freight',
                'severity' => $pct > 18 ? 'high' : 'medium',
                'title'    => "Frete alto — {$row->category}",
                'message'  => "Impacto médio de {$pct}% do frete no custo total desta categoria ({$row->invoice_count} notas)",
                'detail'   => "Considere renegociar frete ou buscar fornecedores mais próximos",
                'date'     => now()->toDateString(),
            ];
        }

        // 3. Consecutive price increase (2+ months rising)
        $monthlyPrices = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->select([
                'ii.category',
                DB::raw("DATE_FORMAT(i.issue_date, '%Y-%m') as month"),
                DB::raw('AVG(ii.unit_price) as avg_price'),
            ])
            ->groupBy('ii.category', 'month')
            ->orderBy('ii.category')
            ->orderBy('month')
            ->get()
            ->groupBy('category');

        foreach ($monthlyPrices as $category => $months) {
            $monthList  = $months->values();
            $consecutive = 0;
            $startPrice  = null;

            for ($i = 1; $i < count($monthList); $i++) {
                if ($monthList[$i]->avg_price > $monthList[$i - 1]->avg_price) {
                    $consecutive++;
                    if ($consecutive === 1) {
                        $startPrice = $monthList[$i - 1]->avg_price;
                    }
                } else {
                    $consecutive = 0;
                    $startPrice  = null;
                }
            }

            if ($consecutive >= 2 && $startPrice) {
                $lastPrice  = (float) $monthList->last()->avg_price;
                $totalRise  = round((($lastPrice - $startPrice) / $startPrice) * 100, 1);
                $alerts[]   = [
                    'type'     => 'consecutive_rise',
                    'severity' => $consecutive >= 3 ? 'high' : 'medium',
                    'title'    => "Alta consecutiva de preços — {$category}",
                    'message'  => "{$consecutive} meses seguidos de alta · acumulado +{$totalRise}% no preço médio",
                    'detail'   => "Avalie renegociação ou substituição de fornecedor para esta categoria",
                    'date'     => now()->toDateString(),
                ];
            }
        }

        // 4. Supplier concentration: 1 supplier > 70% of a category spend
        $categorySupplier = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('suppliers as s', 's.id', '=', 'i.supplier_id')
            ->when(app()->has('current_company_id'), fn($q) => $q->where('i.company_id', app('current_company_id')))
            ->select([
                'ii.category',
                's.name as supplier_name',
                DB::raw('SUM(ii.total_price) as supplier_total'),
            ])
            ->groupBy('ii.category', 's.id', 's.name')
            ->orderBy('ii.category')
            ->orderByDesc('supplier_total')
            ->get()
            ->groupBy('category');

        foreach ($categorySupplier as $category => $rows) {
            $total       = $rows->sum('supplier_total');
            $topSupplier = $rows->first();
            $share       = $total > 0 ? round(($topSupplier->supplier_total / $total) * 100, 1) : 0;

            if ($share > 70) {
                $alerts[] = [
                    'type'     => 'concentration_risk',
                    'severity' => $share > 90 ? 'high' : 'medium',
                    'title'    => "Concentração de fornecedor — {$category}",
                    'message'  => "{$topSupplier->supplier_name} representa {$share}% das compras desta categoria",
                    'detail'   => "Alta dependência de um único fornecedor é risco operacional e de preço",
                    'date'     => now()->toDateString(),
                ];
            }
        }

        usort($alerts, function ($a, $b) {
            $severityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            $cmpSev        = ($severityOrder[$a['severity']] ?? 2) - ($severityOrder[$b['severity']] ?? 2);
            if ($cmpSev !== 0) return $cmpSev;
            return strcmp($b['date'], $a['date']);
        });

        return response()->json([
            'total'  => count($alerts),
            'high'   => count(array_filter($alerts, fn($a) => $a['severity'] === 'high')),
            'medium' => count(array_filter($alerts, fn($a) => $a['severity'] === 'medium')),
            'alerts' => $alerts,
        ]);
    }
}
