<?php

namespace App\DataCore\Models;

use App\DataCore\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawIngestion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'channel',
        'source',
        'status',
        'payload',
        'payload_hash',
        'error_message',
        'attempts',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'attempts'     => 'integer',
    ];

    // Desabilita updated_at (tabela tem apenas created_at)
    const UPDATED_AT = null;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function markAsPending(): void
    {
        $this->update([
            'status'        => 'pending',
            'error_message' => null,
        ]);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status'   => 'processing',
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function markAsDone(): void
    {
        $this->update([
            'status'       => 'done',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status'        => 'failed',
            'error_message' => $error,
        ]);
    }
}
