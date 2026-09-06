<?php

namespace App\Support\Models\Concerns;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Global scope de tenant + fill de company_id (R-TEN-02).
 *
 * @mixin Model
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute('company_id') !== null) {
                return;
            }

            $companyId = app(CurrentCompany::class)->id();

            if ($companyId !== null) {
                $model->setAttribute('company_id', $companyId);
            }
        });

        static::addGlobalScope('company', function (Builder $builder): void {
            $companyId = app(CurrentCompany::class)->id();

            if ($companyId !== null) {
                $builder->where(
                    $builder->getModel()->getTable().'.company_id',
                    $companyId,
                );
            }
        });
    }

    public function belongsToCurrentCompany(): bool
    {
        $companyId = app(CurrentCompany::class)->id();

        return $companyId !== null
            && (int) $this->getAttribute('company_id') === $companyId;
    }
}
