<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\VatTypeStatus;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\VatType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VatType> */
class VatTypeFactory extends Factory
{
    protected $model = VatType::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'name' => fake()->unique()->words(2, true),
            'rate' => fake()->randomElement(['21.00', '10.00', '4.00', '0.00']),
            'equivalence_surcharge' => fake()->optional()->randomElement(['5.20', '1.40', '0.50']),
            'status' => VatTypeStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => VatTypeStatus::Inactive]);
    }
}
