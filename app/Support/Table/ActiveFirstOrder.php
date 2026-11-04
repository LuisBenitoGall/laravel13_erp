<?php

namespace App\Support\Table;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Orden por defecto Active-first para Indexes Admin con columna `status` string.
 *
 * Usar **solo** cuando `$request->sorts() === []`. Si el cliente envía sorts,
 * no llamar a este helper (TableBuilder aplica solo los del request).
 */
final class ActiveFirstOrder
{
    /**
     * @param  Builder<covariant Model>  $query
     * @return Builder<covariant Model>
     */
    public static function apply(
        Builder $query,
        BackedEnum|string $activeStatus,
        ?string $secondaryColumn = null,
    ): Builder {
        $activeValue = $activeStatus instanceof BackedEnum
            ? (string) $activeStatus->value
            : $activeStatus;

        $query->orderByRaw(
            'CASE WHEN `status` = ? THEN 0 ELSE 1 END',
            [$activeValue],
        );

        if ($secondaryColumn !== null && $secondaryColumn !== '') {
            $query->orderBy($secondaryColumn);
        }

        return $query;
    }
}
