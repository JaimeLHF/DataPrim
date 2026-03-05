<?php

namespace App\DataCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketBenchmarkIndex extends Model
{
    protected $table = 'market_benchmark_indexes';

    protected $fillable = [
        'category_master_id',
        'period',
        'region',
        'avg_percentage',
        'median_percentage',
        'p25_percentage',
        'p75_percentage',
        'std_deviation',
        'sample_size',
        'total_spend_pool',
        'avg_spend_per_company',
        'is_valid',
        'min_sample_threshold',
        'version',
        'calculated_at',
    ];

    protected $casts = [
        'avg_percentage'       => 'float',
        'median_percentage'    => 'float',
        'p25_percentage'       => 'float',
        'p75_percentage'       => 'float',
        'std_deviation'        => 'float',
        'sample_size'          => 'integer',
        'total_spend_pool'     => 'float',
        'avg_spend_per_company' => 'float',
        'is_valid'             => 'boolean',
        'min_sample_threshold' => 'integer',
        'version'              => 'integer',
        'calculated_at'        => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function categoryMaster(): BelongsTo
    {
        return $this->belongsTo(CategoryMaster::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    public function scopeForRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderByDesc('version');
    }

    /**
     * Retorna o índice mais recente para uma combinação de categoria/período/região.
     */
    public static function latestFor(int $categoryMasterId, string $period, string $region): ?self
    {
        return static::where('category_master_id', $categoryMasterId)
            ->where('period', $period)
            ->where('region', $region)
            ->orderByDesc('version')
            ->first();
    }
}
