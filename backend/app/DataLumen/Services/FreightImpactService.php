<?php

namespace App\DataLumen\Services;

use App\DataCore\Repositories\InvoiceRepository;
use Illuminate\Support\Collection;

class FreightImpactService
{
    public function __construct(
        private InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Calcula impacto percentual do frete no custo total, por categoria.
     * Fórmula: (sum(freight) / sum(total_value)) * 100
     */
    public function freightImpactByCategory(?string $startDate = null, ?string $endDate = null): Collection
    {
        return $this->invoiceRepository
            ->getFreightImpactByCategory($startDate, $endDate)
            ->map(function ($row) {
                $total   = max((float) $row->total_invoiced, 0.01);
                $freight = (float) $row->total_freight;
                $percent = round(($freight / $total) * 100, 2);

                return [
                    'category'        => $row->category,
                    'total_invoiced'  => round($total, 2),
                    'total_freight'   => round($freight, 2),
                    'freight_percent' => $percent,
                ];
            });
    }

    /**
     * Percentual médio geral de frete sobre o total.
     */
    public function overallFreightPercent(?string $startDate = null, ?string $endDate = null): float
    {
        $categories = $this->freightImpactByCategory($startDate, $endDate);

        if ($categories->isEmpty()) {
            return 0.0;
        }

        return round($categories->avg('freight_percent'), 2);
    }
}
