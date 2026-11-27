<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\DayType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteDayType
{
    public function handle(DayType $dayType): void
    {
        if ($dayType->workCalendarDays()->exists()) {
            throw ValidationException::withMessages([
                'day_type' => __('tipo_jornada_en_uso'),
            ]);
        }

        DB::transaction(fn () => $dayType->delete());
    }
}
