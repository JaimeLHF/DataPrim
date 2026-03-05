<?php

namespace App\DataForge\Services;

use App\DataCore\Repositories\CompanyCostStructureRepository;
use App\DataCore\Repositories\MarketBenchmarkRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MarketBenchmarkCalculatorService
{
    /** Mínimo de empresas para considerar benchmark válido */
    private const MIN_SAMPLE_SIZE = 3;

    public function __construct(
        private CompanyCostStructureRepository $costRepo,
        private MarketBenchmarkRepository $benchmarkRepo
    ) {}

    /**
     * Calcula e persiste os índices de benchmark de mercado para um período.
     *
     * Para cada combinação (category_master_id, period, region):
     *   1. Coleta percentuais de várias empresas
     *   2. Remove outliers via IQR (1.5×)
     *   3. Calcula avg, median, p25, p75, std_deviation
     *   4. Marca is_valid = sample_size >= MIN_SAMPLE_SIZE
     *
     * @return array  ['indexes_created' => int, 'skipped_invalid' => int]
     */
    public function calculateForPeriod(string $period, ?string $region = null): array
    {
        $combinations = $this->costRepo->getDistinctCombinations($period, $region);

        if ($combinations->isEmpty()) {
            return ['indexes_created' => 0, 'skipped_invalid' => 0];
        }

        $nextVersion = $this->benchmarkRepo->getNextVersion($period, $region);
        $created     = 0;
        $skipped     = 0;
        $now         = Carbon::now();

        foreach ($combinations as $combo) {
            $catId     = $combo->category_master_id;
            $comboRegion = $combo->region;

            // 1. Coletar percentuais de todas as empresas
            $percentages = $this->costRepo->getPercentagesForBenchmark($catId, $period, $comboRegion);

            if ($percentages->isEmpty()) {
                $skipped++;
                continue;
            }

            // 2. Remover outliers via IQR
            $cleaned = $this->removeOutliersIqr($percentages->pluck('percentage')->map(fn ($v) => (float) $v));

            if ($cleaned->isEmpty()) {
                $skipped++;
                continue;
            }

            // 3. Calcular estatísticas
            $stats = $this->calculateStatistics($cleaned);

            // 4. Total spend pool e avg por empresa
            $totalSpendPool   = $percentages->sum('total_spend');
            $avgSpendPerCompany = $percentages->count() > 0
                ? $totalSpendPool / $percentages->count()
                : 0;

            // 5. Validar amostra
            $sampleSize = $cleaned->count();
            $isValid    = $sampleSize >= self::MIN_SAMPLE_SIZE;

            // 6. Persistir
            $this->benchmarkRepo->upsert([
                'category_master_id'    => $catId,
                'period'                => $period,
                'region'                => $comboRegion,
                'avg_percentage'        => round($stats['avg'], 3),
                'median_percentage'     => round($stats['median'], 3),
                'p25_percentage'        => round($stats['p25'], 3),
                'p75_percentage'        => round($stats['p75'], 3),
                'std_deviation'         => round($stats['std_deviation'], 3),
                'sample_size'           => $sampleSize,
                'total_spend_pool'      => round($totalSpendPool, 2),
                'avg_spend_per_company' => round($avgSpendPerCompany, 2),
                'is_valid'              => $isValid,
                'min_sample_threshold'  => self::MIN_SAMPLE_SIZE,
                'version'               => $nextVersion,
                'calculated_at'         => $now,
            ]);

            $created++;

            if (!$isValid) {
                $skipped++;
            }
        }

        return [
            'indexes_created' => $created,
            'skipped_invalid' => $skipped,
        ];
    }

    /**
     * Calcula benchmark para todos os períodos disponíveis.
     */
    public function calculateAllPeriods(?string $region = null): array
    {
        $periods = $this->benchmarkRepo->getAvailablePeriods();

        if ($periods->isEmpty()) {
            // Se não houver benchmark anterior, busca períodos das cost structures
            $periods = $this->costRepo->getDistinctCombinations(null, $region)
                ->pluck('period')
                ->unique()
                ->sort()
                ->values();
        }

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($periods as $period) {
            $result = $this->calculateForPeriod($period, $region);
            $totalCreated += $result['indexes_created'];
            $totalSkipped += $result['skipped_invalid'];
        }

        return [
            'periods_processed' => $periods->count(),
            'indexes_created'   => $totalCreated,
            'skipped_invalid'   => $totalSkipped,
        ];
    }

    /**
     * Remove outliers usando método IQR (Interquartile Range).
     * Valores fora de [Q1 - 1.5*IQR, Q3 + 1.5*IQR] são excluídos.
     */
    private function removeOutliersIqr(Collection $values): Collection
    {
        if ($values->count() < 4) {
            return $values; // Pouca amostra, não eliminar
        }

        $sorted = $values->sort()->values();
        $q1     = $this->percentile($sorted, 25);
        $q3     = $this->percentile($sorted, 75);
        $iqr    = $q3 - $q1;

        $lowerBound = $q1 - (1.5 * $iqr);
        $upperBound = $q3 + (1.5 * $iqr);

        return $sorted->filter(fn ($v) => $v >= $lowerBound && $v <= $upperBound)->values();
    }

    /**
     * Calcula avg, median, p25, p75, std_deviation.
     * Implementação em PHP (MySQL não tem PERCENTILE_CONT).
     */
    private function calculateStatistics(Collection $values): array
    {
        $sorted = $values->sort()->values();
        $count  = $sorted->count();

        if ($count === 0) {
            return [
                'avg'           => 0,
                'median'        => 0,
                'p25'           => 0,
                'p75'           => 0,
                'std_deviation' => 0,
            ];
        }

        $avg    = $sorted->avg();
        $median = $this->percentile($sorted, 50);
        $p25    = $this->percentile($sorted, 25);
        $p75    = $this->percentile($sorted, 75);

        // Desvio padrão (populacional)
        $variance = $sorted->reduce(function ($carry, $value) use ($avg) {
            return $carry + pow($value - $avg, 2);
        }, 0) / $count;

        $stdDeviation = sqrt($variance);

        return [
            'avg'           => $avg,
            'median'        => $median,
            'p25'           => $p25,
            'p75'           => $p75,
            'std_deviation' => $stdDeviation,
        ];
    }

    /**
     * Calcula o percentil p de uma coleção ordenada.
     * Usa interpolação linear (mesmo método de Excel PERCENTILE).
     */
    private function percentile(Collection $sorted, float $p): float
    {
        $count = $sorted->count();

        if ($count === 0) {
            return 0;
        }

        if ($count === 1) {
            return $sorted->first();
        }

        $rank = ($p / 100) * ($count - 1);
        $lower = (int) floor($rank);
        $upper = (int) ceil($rank);
        $fraction = $rank - $lower;

        if ($lower === $upper) {
            return $sorted[$lower];
        }

        return $sorted[$lower] + $fraction * ($sorted[$upper] - $sorted[$lower]);
    }
}
