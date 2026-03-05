<?php

namespace App\DataCore\Repositories;

use App\DataCore\Models\InvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceItemRepository
{
    /**
     * Dados para cálculo de TCO por categoria (agrega valores da invoice).
     */
    public function getTcoDataByCategory(?string $startDate = null, ?string $endDate = null): Collection
    {
        return DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->when($startDate, fn($q) => $q->where('inv.issue_date', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->where('inv.issue_date', '<=', $endDate))
            ->when(app()->has('current_company_id'), fn($q) => $q->where('inv.company_id', app('current_company_id')))
            ->select(
                'ii.category',
                DB::raw('SUM(ii.total_price) as total_items_value'),
                DB::raw('SUM(inv.freight_value) as total_freight'),
                DB::raw('SUM(inv.tax_value) as total_tax'),
                DB::raw('COUNT(ii.id) as items_count')
            )
            ->groupBy('ii.category')
            ->orderBy('ii.category')
            ->get();
    }

    /**
     * Média mensal de preço unitário por categoria.
     */
    public function getMonthlyAveragePrice(?string $category = null): Collection
    {
        return DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->when($category, fn($q) => $q->where('ii.category', $category))
            ->when(app()->has('current_company_id'), fn($q) => $q->where('inv.company_id', app('current_company_id')))
            ->select(
                'ii.category',
                DB::raw("DATE_FORMAT(inv.issue_date, '%Y-%m') as month"),
                DB::raw('AVG(ii.unit_price) as avg_unit_price'),
                DB::raw('COUNT(ii.id) as items_count')
            )
            ->groupBy('ii.category', 'month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Min, média e max de preço unitário por categoria.
     */
    public function getPriceDispersion(?string $startDate = null, ?string $endDate = null): Collection
    {
        return DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->when($startDate, fn($q) => $q->where('inv.issue_date', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->where('inv.issue_date', '<=', $endDate))
            ->when(app()->has('current_company_id'), fn($q) => $q->where('inv.company_id', app('current_company_id')))
            ->select(
                'ii.category',
                DB::raw('MIN(ii.unit_price) as min_price'),
                DB::raw('AVG(ii.unit_price) as avg_price'),
                DB::raw('MAX(ii.unit_price) as max_price')
            )
            ->groupBy('ii.category')
            ->orderBy('ii.category')
            ->get();
    }

    /**
     * Ranking de categorias por valor total.
     */
    public function getCategoryRanking(?string $startDate = null, ?string $endDate = null): Collection
    {
        return DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->when($startDate, fn($q) => $q->where('inv.issue_date', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->where('inv.issue_date', '<=', $endDate))
            ->when(app()->has('current_company_id'), fn($q) => $q->where('inv.company_id', app('current_company_id')))
            ->select(
                'ii.category',
                DB::raw('SUM(ii.total_price) as total_value'),
                DB::raw('SUM(ii.quantity) as total_qty'),
                DB::raw('COUNT(ii.id) as items_count')
            )
            ->groupBy('ii.category')
            ->orderByDesc('total_value')
            ->get();
    }
}
