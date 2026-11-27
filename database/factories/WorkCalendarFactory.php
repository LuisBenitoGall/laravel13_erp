<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Calendar\Models\WorkCalendar;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkCalendar> */
class WorkCalendarFactory extends Factory
{
    protected $model = WorkCalendar::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'workplace_id' => function (array $attributes): int {
                return Workplace::factory()->create([
                    'company_id' => $attributes['company_id'],
                ])->id;
            },
        ];
    }

    public function forWorkplace(Workplace $workplace): static
    {
        return $this->state(fn (): array => [
            'company_id' => $workplace->company_id,
            'workplace_id' => $workplace->id,
        ]);
    }
}
