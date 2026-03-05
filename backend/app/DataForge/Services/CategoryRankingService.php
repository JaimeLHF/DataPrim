<?php

namespace App\DataForge\Services;

use App\DataCore\Repositories\InvoiceItemRepository;
use Illuminate\Support\Collection;

class CategoryRankingService
{
    public function __construct(
        private InvoiceItemRepository $itemRepository
    ) {}

    /**
     * Ranking de categorias por valor total investido (desc).
     */
    public function rankingByValue(?string $startDate = null, ?string $endDate = null): Collection
    {
        return $this->itemRepository
            ->getCategoryRanking($startDate, $endDate)
            ->map(function ($row, $index) {
                return [
                    'rank'        => $index + 1,
                    'category'    => $row->category,
                    'total_value' => round((float) $row->total_value, 2),
                    'total_qty'   => round((float) $row->total_qty, 0),
                    'items_count' => (int) $row->items_count,
                ];
            });
    }

    /**
     * Categoria com maior investimento total.
     */
    public function topCategory(?string $startDate = null, ?string $endDate = null): ?array
    {
        return $this->rankingByValue($startDate, $endDate)->first();
    }
}
