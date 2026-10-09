<?php

namespace App\Domains\Partners\Enums;

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
            self::Closed => [],
        };
    }
}
