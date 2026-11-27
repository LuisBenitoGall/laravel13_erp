<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Models\WorkCalendar;
use App\Domains\Calendar\Models\WorkCalendarDay;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class InheritWorkCalendarYear
{
    /**
     * Copia días del año origen al destino (overwrite por fecha; omite 29-feb
     * si el destino no es bisiesto).
     *
     * @return int Número de días upserted
     */
    public function handle(WorkCalendar $workCalendar, int $fromYear, int $toYear): int
    {
        return DB::transaction(function () use ($workCalendar, $fromYear, $toYear): int {
            $sourceDays = WorkCalendarDay::query()
                ->where('work_calendar_id', $workCalendar->id)
                ->whereYear('date', $fromYear)
                ->get();

            $count = 0;
            $toIsLeap = CarbonImmutable::create($toYear, 1, 1)->isLeapYear();

            foreach ($sourceDays as $source) {
                $month = (int) $source->date->format('n');
                $day = (int) $source->date->format('j');

                if ($month === 2 && $day === 29 && ! $toIsLeap) {
                    continue;
                }

                $targetDate = CarbonImmutable::create($toYear, $month, $day)->toDateString();

                $existing = WorkCalendarDay::query()
                    ->where('work_calendar_id', $workCalendar->id)
                    ->whereDate('date', $targetDate)
                    ->first();

                if ($existing === null) {
                    WorkCalendarDay::query()->create([
                        'work_calendar_id' => $workCalendar->id,
                        'date' => $targetDate,
                        'name' => $source->name,
                        'day_type_id' => $source->day_type_id,
                    ]);
                } else {
                    $existing->update([
                        'name' => $source->name,
                        'day_type_id' => $source->day_type_id,
                    ]);
                }

                $count++;
            }

            return $count;
        });
    }
}
