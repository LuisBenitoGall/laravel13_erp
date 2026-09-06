<?php

namespace App\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Rellena created_by / updated_by en creating/updating.
 *
 * @mixin Model
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::creating(function (Model $model): void {
            $userId = Auth::id();

            if ($userId === null) {
                return;
            }

            if ($model->getAttribute('created_by') === null) {
                $model->setAttribute('created_by', $userId);
            }

            if ($model->getAttribute('updated_by') === null) {
                $model->setAttribute('updated_by', $userId);
            }
        });

        static::updating(function (Model $model): void {
            $userId = Auth::id();

            if ($userId !== null) {
                $model->setAttribute('updated_by', $userId);
            }
        });
    }
}
