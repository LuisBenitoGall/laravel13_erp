<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\WorkCalendarDay;
use Illuminate\Support\Facades\DB;

class DeleteWorkCalendarDay
{
    public function handle(WorkCalendarDay $day): void
    {
        DB::transaction(fn () => $day->delete());
    }
}
