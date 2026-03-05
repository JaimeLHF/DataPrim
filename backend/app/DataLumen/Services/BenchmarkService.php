<?php

namespace App\DataLumen\Services;

use Illuminate\Support\Collection;

class BenchmarkService
{
    /**
     * Fatores de variação de mercado por categoria (base fictícia).
     */
    private array $marketFactors = [
        'MDF'        => 1.08,
        'Ferragens'  => 0.95,
        'Químicos'   => 1.12,
        'Aramados'   => 0.97,
        'Embalagens' => 1.05,
        'Acessórios' => 1.03,
    ];

    /**
     * Gera média de mercado fictícia e calcula variação percentual.
     *
     * @param  Collection $companyTco  Output de TcoCalculatorService::tcoAverageByCategory()
     * @return Collection
     */
    public function compareWithMarket(Collection $companyTco): Collection
    {
        return $companyTco->map(function ($row) {
            $factor       = $this->marketFactors[$row['category']] ?? 1.0;
            // Pequena variação aleatória determinística por categoria
            $seed         = crc32($row['category']);
            srand($seed);
            $jitter       = (rand(-50, 50) / 1000); // ±5%
            srand(); // reset
            $marketAvg    = round($row['tco_average'] * ($factor + $jitter), 2);
            $variation    = $marketAvg > 0
                ? round((($row['tco_average'] - $marketAvg) / $marketAvg) * 100, 2)
                : 0;

            return [
                'category'          => $row['category'],
                'company_tco_avg'   => $row['tco_average'],
                'market_tco_avg'    => $marketAvg,
                'variation_percent' => $variation,
                'status'            => $variation <= 0 ? 'below_market' : 'above_market',
            ];
        });
    }

    /**
     * Retorna o fator de mercado para uma categoria.
     */
    public function getMarketFactor(string $category): float
    {
        return $this->marketFactors[$category] ?? 1.0;
    }

    /**
     * Retorna todos os fatores de mercado.
     */
    public function getMarketFactors(): array
    {
        return $this->marketFactors;
    }

    /**
     * Gera breakdown de TCO do mercado (para gráfico cascata).
     */
    public function marketTcoBreakdown(Collection $companyBreakdown): Collection
    {
        return $companyBreakdown->map(function ($row) {
            $factor = $this->marketFactors[$row['category']] ?? 1.0;
            $seed = crc32($row['category']);
            srand($seed);
            $jitter = (rand(-50, 50) / 1000);
            srand();
            $totalFactor = $factor + $jitter;

            return [
                'category'       => $row['category'],
                'avg_unit_price' => round($row['avg_unit_price'] * $totalFactor, 2),
                'avg_freight'    => round($row['avg_freight'] * $totalFactor, 2),
                'avg_tax'        => round($row['avg_tax'] * $totalFactor, 2),
                'tco_total'      => round($row['tco_total'] * $totalFactor, 2),
            ];
        });
    }

    /**
     * Variação percentual geral ponderada.
     */
    public function overallVariation(Collection $benchmarkData): float
    {
        if ($benchmarkData->isEmpty()) {
            return 0.0;
        }

        return round($benchmarkData->avg('variation_percent'), 2);
    }
}
