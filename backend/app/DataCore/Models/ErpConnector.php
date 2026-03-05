<?php

namespace App\DataCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ErpConnector extends Model
{
    protected $fillable = [
        'company_id',
        'erp_type',
        'credentials',
        'config',
        'sync_frequency',
        'last_synced_at',
        'last_sync_status',
        'last_sync_error',
        'is_active',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    protected $casts = [
        'config'           => 'array',
        'is_active'        => 'boolean',
        'last_synced_at'   => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    // Campos nunca expostos no JSON
    protected $hidden = ['credentials', 'access_token', 'refresh_token'];

    // ─── Criptografia automática de credenciais ───────────────────────────

    /**
     * Criptografa automaticamente ao setar credentials.
     * Aceita array (serializa) ou string json já formatada.
     */
    public function setCredentialsAttribute(array|string $value): void
    {
        $json = is_array($value) ? json_encode($value) : $value;
        $this->attributes['credentials'] = Crypt::encryptString($json);
    }

    /**
     * Descriptografa automaticamente ao ler credentials.
     * Retorna array associativo.
     */
    public function getCredentialsAttribute(?string $value): array
    {
        if (!$value) return [];
        return json_decode(Crypt::decryptString($value), true) ?? [];
    }

    // ─── Relações ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    /**
     * Conectores ativos cujo intervalo de sync já venceu.
     * Usado pelo comando erp:sync para selecionar quais sincronizar.
     *
     * Usa Carbon para compatibilidade SQLite (testes) e MySQL (produção).
     * Não usa TIMESTAMPDIFF pois é MySQL-only.
     */
    public function scopeDue($query)
    {
        $threshold = now()->subMinutes((int) ($this->sync_frequency ?? 360))->toDateTimeString();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($threshold) {
                $q->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<=', $threshold);
            });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
