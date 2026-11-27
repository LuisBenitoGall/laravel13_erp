<?php

namespace App\Domains\Catalog\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    /** Transiciones válidas Active ↔ Inactive (R-CAP-06). */
    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return in_array($target, match ($this) {
            self::Active => [self::Inactive],
            self::Inactive => [self::Active],
        }, true);
    }
}
