<?php

namespace App\DataCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryMaster extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'group',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function costStructures(): HasMany
    {
        return $this->hasMany(CompanyCostStructure::class);
    }

    public function benchmarkIndexes(): HasMany
    {
        return $this->hasMany(MarketBenchmarkIndex::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
