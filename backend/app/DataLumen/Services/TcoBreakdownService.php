<?php

namespace App\DataLumen\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TcoBreakdownService
{
    /**
     * Retorna composição do TCO por categoria:
     * preço unitário médio, frete médio por item, imposto médio por item.
     */
    public function breakdownByCategory(?string $startDate = null, ?string $endDate = null): Collection
    {
        $rows = DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->when($startDate, fn($q) => $q->where('inv.issue_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('inv.issue_date', '<=', $endDate))
            ->when(app()->has('current_company_id'), fn($q) => $q->where('inv.company_id', app('current_company_id')))
            ->select(
                'ii.category',
                DB::raw('AVG(ii.unit_price) as avg_unit_price'),
                DB::raw('SUM(inv.freight_value) / COUNT(ii.id) as avg_freight'),
                DB::raw('SUM(inv.tax_value) / COUNT(ii.id) as avg_tax'),
                DB::raw('COUNT(ii.id) as items_count')
            )
            ->groupBy('ii.category')
            ->orderBy('ii.category')
            ->get();

        return $rows->map(fn($row) => [
            'category'       => $row->category,
            'avg_unit_price' => round((float) $row->avg_unit_price, 2),
            'avg_freight'    => round((float) $row->avg_freight, 2),
            'avg_tax'        => round((float) $row->avg_tax, 2),
            'tco_total'      => round((float) $row->avg_unit_price + (float) $row->avg_freight + (float) $row->avg_tax, 2),
        ]);
    }
}
