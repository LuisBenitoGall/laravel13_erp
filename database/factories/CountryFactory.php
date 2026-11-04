<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Country> */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->country());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => (string) fake()->numberBetween(1, 999),
            'alfa2' => strtoupper(fake()->lexify('??')),
            'alfa3' => strtoupper(fake()->lexify('???')),
            'flag' => null,
            'status' => GeographyStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => GeographyStatus::Inactive]);
    }
}
