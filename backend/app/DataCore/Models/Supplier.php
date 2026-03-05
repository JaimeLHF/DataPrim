<?php

namespace App\DataCore\Models;

use App\DataCore\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'cnpj',
        'region',
        'state',
        'contact_name',
        'contact_email',
        'contact_phone',
        'payment_terms',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
