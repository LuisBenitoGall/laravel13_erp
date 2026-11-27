<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\DayType;
use Illuminate\Support\Facades\DB;

class CreateDayType
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): DayType
    {
        return DB::transaction(fn (): DayType => DayType::query()->create($data));
    }
}
