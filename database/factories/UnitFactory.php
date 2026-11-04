<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\UnitStatus;
use App\Domains\Admin\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Unit> */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->word());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'symbol' => strtoupper(fake()->lexify('??')),
            'status' => UnitStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => UnitStatus::Inactive]);
    }
}
