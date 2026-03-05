<?php

namespace App\DataCore\Models;

use App\DataCore\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'company_id',
        'source_system',
        'source_id',
        'supplier_id',
        'invoice_number',
        'issue_date',
        'delivery_date',
        'total_value',
        'freight_value',
        'tax_value',
        'payment_terms',
    ];

    protected $casts = [
        'issue_date'    => 'date',
        'delivery_date' => 'date',
        'total_value'   => 'float',
        'freight_value' => 'float',
        'tax_value'     => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
