<?php

namespace App\DataLumen\Services;

use App\DataCore\Models\CategoryMaster;
use App\DataCore\Models\Company;
use App\DataCore\Repositories\CompanyCostStructureRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CostStructureCalculatorService
{
    public function __construct(
        private CompanyCostStructureRepository $repository
    ) {}

    /**
     * Calcula e persiste a estrutura de custo percentual de uma empresa para um período.
     *
     * Para cada categoria com dados no período:
     *   percentage = (total_spend / total_company_spend) * 100
     *
     * Adicionalmente, injeta frete como pseudo-categoria "frete" se houver freight_value.
     *
     * @return Collection  Registros persistidos de CompanyCostStructure
     */
    public function calculateForPeriod(int $companyId, string $period): Collection
    {
        $company = Company::findOrFail($companyId);
        $region  = $company->region ?? 'Desconhecida';

        // 1. Gastos por categoria (itens de nota fiscal)
        $rawSpend = $this->repository->getRawSpendByCategory($companyId, $period);

        if ($rawSpend->isEmpty()) {
            return collect();
        }

        // 2. Total geral da empresa no período (itens)
        $totalItemsSpend = $rawSpend->sum('total_spend');

        // 3. Frete total (vem da invoice, não dos itens)
        $totalFreight = $this->repository->getTotalFreight($companyId, $period);

        // 4. Total geral incluindo frete como componente separado
        $totalCompanySpend = $totalItemsSpend + $totalFreight;

        if ($totalCompanySpend <= 0) {
            return collect();
        }

        $now     = Carbon::now();
        $results = collect();

        // 5. Persistir cada categoria de itens
        foreach ($rawSpend as $row) {
            $spend      = (float) $row->total_spend;
            $percentage = round(($spend / $totalCompanySpend) * 100, 3);

            $record = $this->repository->upsert([
                'company_id'         => $companyId,
                'category_master_id' => $row->category_master_id,
                'period'             => $period,
                'region'             => $region,
                'total_spend'        => round($spend, 2),
                'total_company_spend' => round($totalCompanySpend, 2),
                'percentage'         => $percentage,
                'freight_component'  => round((float) $row->freight_component, 2),
                'tax_component'      => round((float) $row->tax_component, 2),
                'items_count'        => (int) $row->items_count,
                'calculated_at'      => $now,
            ]);

            $results->push($record);
        }

        // 6. Frete como pseudo-categoria
        if ($totalFreight > 0) {
            $freightCategory = CategoryMaster::where('slug', 'frete')->first();

            if ($freightCategory) {
                $freightPercentage = round(($totalFreight / $totalCompanySpend) * 100, 3);

                $record = $this->repository->upsert([
                    'company_id'         => $companyId,
                    'category_master_id' => $freightCategory->id,
                    'period'             => $period,
                    'region'             => $region,
                    'total_spend'        => round($totalFreight, 2),
                    'total_company_spend' => round($totalCompanySpend, 2),
                    'percentage'         => $freightPercentage,
                    'freight_component'  => round($totalFreight, 2),
                    'tax_component'      => 0,
                    'items_count'        => 0,
                    'calculated_at'      => $now,
                ]);

                $results->push($record);
            }
        }

        return $results;
    }

    /**
     * Calcula estrutura de custo para todos os períodos disponíveis de uma empresa.
     *
     * @return array  ['periods_processed' => int, 'records_created' => int]
     */
    public function calculateAllPeriods(int $companyId): array
    {
        $periods = $this->repository->getAvailablePeriods($companyId);
        $totalRecords = 0;

        foreach ($periods as $period) {
            $results = $this->calculateForPeriod($companyId, $period);
            $totalRecords += $results->count();
        }

        return [
            'periods_processed' => $periods->count(),
            'records_created'   => $totalRecords,
        ];
    }
}
