<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Admin\Models\Workplace;
use App\Domains\Calendar\Models\WorkCalendar;
use App\Domains\Calendar\Models\WorkCalendarDay;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CopyWorkCalendarYear
{
    /**
     * Copia días del año Y desde el calendario de W1 al de W2 (misma company).
     *
     * @return int Número de días upserted
     */
    public function handle(
        WorkCalendar $source,
        Workplace $targetWorkplace,
        int $year,
        FindOrCreateWorkCalendar $findOrCreate,
    ): int {
        return DB::transaction(function () use ($source, $targetWorkplace, $year, $findOrCreate): int {
            if ((int) $targetWorkplace->company_id !== (int) $source->company_id) {
                throw new InvalidArgumentException('Target workplace belongs to another company.');
            }

            $target = $findOrCreate->handle($targetWorkplace);

            $sourceDays = WorkCalendarDay::query()
                ->where('work_calendar_id', $source->id)
                ->whereYear('date', $year)
                ->get();

            $count = 0;

            foreach ($sourceDays as $sourceDay) {
                $date = $sourceDay->date->toDateString();

                $existing = WorkCalendarDay::query()
                    ->where('work_calendar_id', $target->id)
                    ->whereDate('date', $date)
                    ->first();

                if ($existing === null) {
                    WorkCalendarDay::query()->create([
                        'work_calendar_id' => $target->id,
                        'date' => $date,
                        'name' => $sourceDay->name,
                        'day_type_id' => $sourceDay->day_type_id,
                    ]);
                } else {
                    $existing->update([
                        'name' => $sourceDay->name,
                        'day_type_id' => $sourceDay->day_type_id,
                    ]);
                }

                $count++;
            }

            return $count;
        });
    }
}
