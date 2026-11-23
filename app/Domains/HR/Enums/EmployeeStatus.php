<?php

namespace App\Domains\HR\Enums;

use Carbon\CarbonInterface;

enum EmployeeStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Ended = 'ended';

    /**
     * Estado inicial de un periodo nuevo: futuro → scheduled; con fin ya pasado
     * → ended; resto → active (como v1).
     */
    public static function initialFor(CarbonInterface $startDate, ?CarbonInterface $endDate = null): self
    {
        $today = now()->startOfDay();

        if ($startDate->copy()->startOfDay()->greaterThan($today)) {
            return self::Scheduled;
        }

        if ($endDate !== null && $endDate->copy()->startOfDay()->lessThan($today)) {
            return self::Ended;
        }

        return self::Active;
    }
}
