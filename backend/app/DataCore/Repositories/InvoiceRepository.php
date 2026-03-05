<?php

namespace App\DataCore\Repositories;

use App\DataCore\Models\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceRepository
{
    public function findByNumber(string $invoiceNumber): ?Invoice
    {
        return Invoice::where('invoice_number', $invoiceNumber)->first();
    }

    /**
     * Dados de frete por categoria (via join com itens).
     */
    public function getFreightImpactByCategory(?string $startDate = null, ?string $endDate = null): Collection
    {
        return DB::table('invoices as inv')
            ->join('invoice_items as ii', 'ii.invoice_id', '=', 'inv.id')
            ->when($startDate, fn($q) => $q->where('inv.issue_date', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->where('inv.issue_date', '<=', $endDate))
            ->when(app()->has('current_company_id'), fn($q) => $q->where('inv.company_id', app('current_company_id')))
            ->select(
                'ii.category',
                DB::raw('SUM(ii.total_price) as total_invoiced'),
                DB::raw('SUM(inv.freight_value) as total_freight')
            )
            ->groupBy('ii.category')
            ->orderBy('ii.category')
            ->get();
    }
}
