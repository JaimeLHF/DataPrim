<?php

namespace App\DataCore\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait BelongsToTenant
 *
 * Adiciona isolamento automático de dados por empresa (tenant) em todos os Models que o usam.
 * - Global Scope: qualquer query filtra automaticamente pela empresa atual
 * - Boot creating: preenche company_id automaticamente ao criar registros
 *
 * Uso: adicionar `use BelongsToTenant;` no Model.
 * O middleware ResolveTenant deve ter sido executado antes (injeta 'current_company_id' no container).
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Global Scope — filtra por company_id em toda query
        static::addGlobalScope('tenant', function (Builder $query) {
            if (app()->has('current_company_id')) {
                $query->where(
                    (new static())->getTable() . '.company_id',
                    app('current_company_id')
                );
            }
        });

        // Preenche company_id automaticamente ao criar registros
        static::creating(function (Model $model) {
            if (app()->has('current_company_id') && empty($model->company_id)) {
                $model->company_id = app('current_company_id');
            }
        });
    }
}
