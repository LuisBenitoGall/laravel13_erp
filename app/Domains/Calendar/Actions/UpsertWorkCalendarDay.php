<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\DayType;
use App\Domains\Calendar\Models\WorkCalendar;
use App\Domains\Calendar\Models\WorkCalendarDay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpsertWorkCalendarDay
{
    /**
     * @param  array{date: string, name: string, day_type_id: int}  $data
     */
    public function handle(WorkCalendar $workCalendar, array $data): WorkCalendarDay
    {
        return DB::transaction(function () use ($workCalendar, $data): WorkCalendarDay {
            $dayType = DayType::query()->findOrFail($data['day_type_id']);

            if ((int) $dayType->company_id !== (int) $workCalendar->company_id) {
                throw new InvalidArgumentException('DayType belongs to another company.');
            }

            $date = Carbon::parse($data['date'])->toDateString();

            $day = WorkCalendarDay::query()
                ->where('work_calendar_id', $workCalendar->id)
                ->whereDate('date', $date)
                ->first();

            if ($day === null) {
                $day = WorkCalendarDay::query()->create([
                    'work_calendar_id' => $workCalendar->id,
                    'date' => $date,
                    'name' => $data['name'],
                    'day_type_id' => $dayType->id,
                ]);
            } else {
                $day->update([
                    'name' => $data['name'],
                    'day_type_id' => $dayType->id,
                ]);
            }

            return $day->refresh();
        });
    }
}
