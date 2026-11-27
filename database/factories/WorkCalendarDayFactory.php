<?php

namespace Database\Factories;

use App\Domains\Calendar\Models\DayType;
use App\Domains\Calendar\Models\WorkCalendar;
use App\Domains\Calendar\Models\WorkCalendarDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkCalendarDay> */
class WorkCalendarDayFactory extends Factory
{
    protected $model = WorkCalendarDay::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'work_calendar_id' => WorkCalendar::factory(),
            'date' => fake()->unique()->date(),
            'name' => fake()->words(2, true),
            'day_type_id' => DayType::factory(),
        ];
    }
}
