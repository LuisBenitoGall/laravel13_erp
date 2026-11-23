<?php

namespace App\Domains\Admin\Enums;

enum CompanyStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';

    /** @return list<self> */
    public function transitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active],
            self::Active => [self::Closed],
            // Reabrir desde cerrada (toggle Index/Edit y Select de ficha).
            self::Closed => [self::Active],
        };
    }
}
