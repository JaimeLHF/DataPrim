<?php

namespace App\DataCore\Repositories;

use App\DataCore\Models\MarketBenchmarkIndex;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MarketBenchmarkRepository
{
    /**
     * Retorna o benchmark mais recente (latest version) para cada categoria num período/região.
     */
    public function getLatestBenchmarks(string $period, string $region): Collection
    {
        // Subquery para pegar a versão mais recente de cada combinação
        $latestVersions = DB::table('market_benchmark_indexes')
            ->select('category_master_id', DB::raw('MAX(version) as max_version'))
            ->where('period', $period)
            ->where('region', $region)
            ->groupBy('category_master_id');

        return MarketBenchmarkIndex::with('categoryMaster')
            ->where('period', $period)
            ->where('region', $region)
            ->where('is_valid', true)
            ->joinSub($latestVersions, 'lv', function ($join) {
                $join->on('market_benchmark_indexes.category_master_id', '=', 'lv.category_master_id')
                     ->on('market_benchmark_indexes.version', '=', 'lv.max_version');
            })
            ->select('market_benchmark_indexes.*')
            ->get();
    }

    /**
     * Retorna benchmark para uma categoria específica.
     */
    public function getLatestForCategory(int $categoryMasterId, string $period, string $region): ?MarketBenchmarkIndex
    {
        return MarketBenchmarkIndex::latestFor($categoryMasterId, $period, $region);
    }

    /**
     * UPSERT de um índice de benchmark.
     */
    public function upsert(array $data): MarketBenchmarkIndex
    {
        return MarketBenchmarkIndex::updateOrCreate(
            [
                'category_master_id' => $data['category_master_id'],
                'period'             => $data['period'],
                'region'             => $data['region'],
                'version'            => $data['version'],
            ],
            $data
        );
    }

    /**
     * Próxima versão para uma combinação period/region.
     * Todos os índices calculados em um mesmo batch compartilham a mesma versão.
     */
    public function getNextVersion(string $period, ?string $region = null): int
    {
        $query = DB::table('market_benchmark_indexes')
            ->where('period', $period);

        if ($region) {
            $query->where('region', $region);
        }

        $current = $query->max('version');

        return ((int) $current) + 1;
    }

    /**
     * Períodos que possuem benchmarks válidos.
     */
    public function getAvailablePeriods(?string $region = null): Collection
    {
        $query = DB::table('market_benchmark_indexes')
            ->where('is_valid', true)
            ->select('period')
            ->distinct();

        if ($region) {
            $query->where('region', $region);
        }

        return $query->orderBy('period')->pluck('period');
    }
}
