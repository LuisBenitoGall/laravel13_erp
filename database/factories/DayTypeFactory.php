<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Calendar\Models\DayType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DayType> */
class DayTypeFactory extends Factory
{
    protected $model = DayType::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->unique()->words(2, true),
            'color' => fake()->hexColor(),
            'is_working' => true,
        ];
    }

    public function nonWorking(): static
    {
        return $this->state(fn (): array => [
            'is_working' => false,
            'color' => '#ef4444',
        ]);
    }
}
