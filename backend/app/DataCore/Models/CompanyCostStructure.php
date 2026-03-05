<?php

namespace App\DataCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCostStructure extends Model
{
    protected $fillable = [
        'company_id',
        'category_master_id',
        'period',
        'region',
        'total_spend',
        'total_company_spend',
        'percentage',
        'freight_component',
        'tax_component',
        'items_count',
        'calculated_at',
    ];

    protected $casts = [
        'total_spend'         => 'float',
        'total_company_spend' => 'float',
        'percentage'          => 'float',
        'freight_component'   => 'float',
        'tax_component'       => 'float',
        'items_count'         => 'integer',
        'calculated_at'       => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function categoryMaster(): BelongsTo
    {
        return $this->belongsTo(CategoryMaster::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    public function scopeForRegion($query, string $region)
    {
        return $query->where('region', $region);
    }
}
