<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Province> */
class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->city());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'country_id' => Country::factory(),
            'status' => GeographyStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => GeographyStatus::Inactive]);
    }
}
