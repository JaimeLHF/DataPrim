<?php

namespace App\DataCore\Repositories;

use App\DataCore\Models\CompanyCostStructure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanyCostStructureRepository
{
    /**
     * Gastos por categoria para uma empresa em um período.
     * Retorna dados brutos de invoice_items agrupados por category_master_id.
     */
    public function getRawSpendByCategory(int $companyId, string $period): Collection
    {
        return DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->where('inv.company_id', $companyId)
            ->whereNotNull('ii.category_master_id')
            ->whereRaw("DATE_FORMAT(inv.issue_date, '%Y-%m') = ?", [$period])
            ->select(
                'ii.category_master_id',
                DB::raw('SUM(ii.total_price) as total_spend'),
                DB::raw('COUNT(ii.id) as items_count'),
                // Rateio proporcional de frete: freight_value * (item_total / invoice_total)
                DB::raw('SUM(CASE WHEN inv.total_value > 0 THEN inv.freight_value * (ii.total_price / inv.total_value) ELSE 0 END) as freight_component'),
                // Rateio proporcional de impostos
                DB::raw('SUM(CASE WHEN inv.total_value > 0 THEN inv.tax_value * (ii.total_price / inv.total_value) ELSE 0 END) as tax_component')
            )
            ->groupBy('ii.category_master_id')
            ->get();
    }

    /**
     * Gasto total da empresa em um período (soma de todos os itens).
     */
    public function getTotalCompanySpend(int $companyId, string $period): float
    {
        $result = DB::table('invoice_items as ii')
            ->join('invoices as inv', 'ii.invoice_id', '=', 'inv.id')
            ->where('inv.company_id', $companyId)
            ->whereNotNull('ii.category_master_id')
            ->whereRaw("DATE_FORMAT(inv.issue_date, '%Y-%m') = ?", [$period])
            ->selectRaw('SUM(ii.total_price) as total')
            ->value('total');

        return (float) ($result ?? 0);
    }

    /**
     * Frete total da empresa em um período.
     */
    public function getTotalFreight(int $companyId, string $period): float
    {
        $result = DB::table('invoices')
            ->where('company_id', $companyId)
            ->whereRaw("DATE_FORMAT(issue_date, '%Y-%m') = ?", [$period])
            ->selectRaw('SUM(freight_value) as total')
            ->value('total');

        return (float) ($result ?? 0);
    }

    /**
     * Períodos (YYYY-MM) distintos com dados para uma empresa.
     */
    public function getAvailablePeriods(int $companyId): Collection
    {
        return DB::table('invoices')
            ->where('company_id', $companyId)
            ->selectRaw("DISTINCT DATE_FORMAT(issue_date, '%Y-%m') as period")
            ->orderBy('period')
            ->pluck('period');
    }

    /**
     * UPSERT: persiste ou atualiza um registro de cost structure.
     */
    public function upsert(array $data): CompanyCostStructure
    {
        return CompanyCostStructure::updateOrCreate(
            [
                'company_id'        => $data['company_id'],
                'category_master_id' => $data['category_master_id'],
                'period'            => $data['period'],
            ],
            $data
        );
    }

    /**
     * Estrutura de custo de uma empresa para um período.
     */
    public function getForCompanyPeriod(int $companyId, string $period): Collection
    {
        return CompanyCostStructure::with('categoryMaster')
            ->forCompany($companyId)
            ->forPeriod($period)
            ->orderBy('percentage', 'desc')
            ->get();
    }

    /**
     * Percentuais de todas as empresas participantes para uma categoria/período/região.
     * Usado pelo MarketBenchmarkCalculatorService para agregar.
     */
    public function getPercentagesForBenchmark(
        int $categoryMasterId,
        string $period,
        ?string $region = null
    ): Collection {
        $query = DB::table('company_cost_structures as ccs')
            ->join('companies as c', 'ccs.company_id', '=', 'c.id')
            ->where('c.is_benchmark_participant', true)
            ->where('ccs.category_master_id', $categoryMasterId)
            ->where('ccs.period', $period);

        if ($region && $region !== 'Nacional') {
            $query->where('ccs.region', $region);
        }

        return $query->select(
            'ccs.company_id',
            'ccs.percentage',
            'ccs.total_spend'
        )->get();
    }

    /**
     * Combinações únicas de (category_master_id, period, region) existentes.
     * Usado para saber quais benchmarks calcular.
     */
    public function getDistinctCombinations(?string $period = null, ?string $region = null): Collection
    {
        $query = DB::table('company_cost_structures')
            ->select('category_master_id', 'period', 'region')
            ->distinct();

        if ($period) {
            $query->where('period', $period);
        }

        if ($region) {
            $query->where('region', $region);
        }

        return $query->get();
    }
}
