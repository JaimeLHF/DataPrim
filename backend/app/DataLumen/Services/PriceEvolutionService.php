<?php

namespace App\DataLumen\Services;

use App\DataCore\Repositories\InvoiceItemRepository;
use Illuminate\Support\Collection;

class PriceEvolutionService
{
    public function __construct(
        private InvoiceItemRepository $itemRepository,
        private BenchmarkService $benchmarkService,
    ) {}

    /**
     * Retorna média mensal de preço unitário por categoria.
     * Agrupado por ano-mês para gráfico de linha temporal.
     */
    public function monthlyAverageByCategory(?string $category = null): Collection
    {
        return $this->itemRepository->getMonthlyAveragePrice($category);
    }

    /**
     * Formata dados para o frontend: lista de meses com valor por categoria.
     * Inclui curva de mercado (prefixo "Mercado ") se $includeMarket = true.
     */
    public function formatForChart(?string $category = null, bool $includeMarket = true): array
    {
        $raw = $this->monthlyAverageByCategory($category);

        // Agrupar por mês
        $byMonth = [];
        $firstPriceByCategory = [];

        foreach ($raw as $row) {
            $month = $row->month;
            if (!isset($byMonth[$month])) {
                $byMonth[$month] = ['month' => $month];
            }
            $price = round((float) $row->avg_unit_price, 2);
            $byMonth[$month][$row->category] = $price;

            // Guardar primeiro preço para calcular curva de mercado
            if (!isset($firstPriceByCategory[$row->category])) {
                $firstPriceByCategory[$row->category] = [
                    'price' => $price,
                    'month' => $month,
                ];
            }
        }

        // Adicionar curva de mercado
        if ($includeMarket && !empty($firstPriceByCategory)) {
            $months = array_keys($byMonth);
            foreach ($firstPriceByCategory as $cat => $base) {
                $factor = $this->benchmarkService->getMarketFactor($cat);
                $baseMonthIndex = array_search($base['month'], $months);

                foreach ($months as $i => $month) {
                    $monthsElapsed = $i - $baseMonthIndex;
                    // Crescimento mensal baseado no fator anual
                    $monthlyFactor = pow($factor, $monthsElapsed / 12);
                    $marketPrice = round($base['price'] * $monthlyFactor, 2);
                    $byMonth[$month]["Mercado {$cat}"] = $marketPrice;
                }
            }
        }

        return array_values($byMonth);
    }

    /**
     * Retorna lista de categorias disponíveis na evolução.
     */
    public function availableCategories(): array
    {
        $raw = $this->monthlyAverageByCategory();
        return $raw->pluck('category')->unique()->values()->toArray();
    }

    /**
     * Retorna dados de índice percentual (base 100) por categoria.
     */
    public function priceIndex(?string $category = null): array
    {
        $raw = $this->monthlyAverageByCategory($category);

        $basePriceByCategory = [];
        $byMonth = [];

        foreach ($raw as $row) {
            $month = $row->month;
            $price = (float) $row->avg_unit_price;

            if (!isset($basePriceByCategory[$row->category])) {
                $basePriceByCategory[$row->category] = $price;
            }

            if (!isset($byMonth[$month])) {
                $byMonth[$month] = ['month' => $month];
            }

            $basePrice = $basePriceByCategory[$row->category];
            $index = $basePrice > 0 ? round(($price / $basePrice) * 100, 1) : 100;
            $byMonth[$month][$row->category] = $index;
        }

        return array_values($byMonth);
    }
}
