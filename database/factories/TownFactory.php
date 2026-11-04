<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Town;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Town> */
class TownFactory extends Factory
{
    protected $model = Town::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->streetName());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'province_id' => Province::factory(),
            'status' => GeographyStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => GeographyStatus::Inactive]);
    }
}
