<?php

namespace App\DataCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WebhookConfig extends Model
{
    protected $fillable = [
        'company_id',
        'erp_type',
        'slug',
        'secret',
        'is_active',
        'last_received_at',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'last_received_at'  => 'datetime',
    ];

    // Campos nunca expostos na serialização
    protected $hidden = ['secret'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Gera um slug único para esta config: "{company-slug}-{erp_type}".
     * Ex: "moveis-ruiz-bling"
     */
    public static function generateSlug(Company $company, string $erpType): string
    {
        $base = Str::slug($company->name) . '-' . $erpType;
        $slug = $base;
        $i    = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Gera um secret aleatório seguro para HMAC.
     */
    public static function generateSecret(): string
    {
        return bin2hex(random_bytes(32)); // 64 chars hex
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
