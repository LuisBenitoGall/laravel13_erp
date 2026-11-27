<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\DayType;
use Illuminate\Support\Facades\DB;

class UpdateDayType
{
    /** @param  array<string, mixed>  $data */
    public function handle(DayType $dayType, array $data): DayType
    {
        return DB::transaction(function () use ($dayType, $data): DayType {
            $dayType->update($data);

            return $dayType->refresh();
        });
    }
}
