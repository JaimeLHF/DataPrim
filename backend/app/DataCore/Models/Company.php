<?php

namespace App\DataCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'cnpj',
        'slug',
        'plan',
        'is_active',
        'region',
        'segment',
        'state',
        'is_benchmark_participant',
        'benchmark_anonymized_id',
    ];

    protected $casts = [
        'is_benchmark_participant' => 'boolean',
        'is_active'               => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_users')
            ->withPivot('role', 'invited_by', 'joined_at')
            ->withTimestamps();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function rawIngestions(): HasMany
    {
        return $this->hasMany(RawIngestion::class);
    }

    public function costStructures(): HasMany
    {
        return $this->hasMany(CompanyCostStructure::class);
    }

    public function scopeBenchmarkParticipants($query)
    {
        return $query->where('is_benchmark_participant', true);
    }
}
