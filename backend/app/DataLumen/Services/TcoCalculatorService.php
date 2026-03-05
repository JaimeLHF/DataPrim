<?php

namespace App\DataLumen\Services;

use App\DataCore\Repositories\InvoiceItemRepository;
use Illuminate\Support\Collection;

class TcoCalculatorService
{
    public function __construct(
        private InvoiceItemRepository $itemRepository
    ) {}

    /**
     * TCO = total_value + freight_value + tax_value
     * Calcula TCO médio por categoria dos itens de nota fiscal.
     */
    public function tcoAverageByCategory(?string $startDate = null, ?string $endDate = null): Collection
    {
        $rows = $this->itemRepository->getTcoDataByCategory($startDate, $endDate);

        return $rows->map(function ($row) {
            $tcoTotal = $row->total_items_value + $row->total_freight + $row->total_tax;
            $count    = max($row->items_count, 1);

            return [
                'category'    => $row->category,
                'tco_total'   => round($tcoTotal, 2),
                'tco_average' => round($tcoTotal / $count, 2),
                'items_count' => $row->items_count,
            ];
        });
    }

    /**
     * TCO geral médio de todas as categorias.
     */
    public function overallTcoAverage(?string $startDate = null, ?string $endDate = null): float
    {
        $categories = $this->tcoAverageByCategory($startDate, $endDate);

        if ($categories->isEmpty()) {
            return 0.0;
        }

        return round($categories->avg('tco_average'), 2);
    }
}
