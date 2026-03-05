<?php

namespace App\DataLumen\Services;

use App\DataCore\Repositories\InvoiceItemRepository;
use Illuminate\Support\Collection;

class PriceDispersionService
{
    public function __construct(
        private InvoiceItemRepository $itemRepository
    ) {}

    /**
     * Retorna min, média e max de preço unitário por categoria.
     */
    public function dispersionByCategory(?string $startDate = null, ?string $endDate = null): Collection
    {
        return $this->itemRepository
            ->getPriceDispersion($startDate, $endDate)
            ->map(function ($row) {
                return [
                    'category'  => $row->category,
                    'min_price' => round((float) $row->min_price, 2),
                    'avg_price' => round((float) $row->avg_price, 2),
                    'max_price' => round((float) $row->max_price, 2),
                    'range'     => round((float) $row->max_price - (float) $row->min_price, 2),
                ];
            });
    }
}
