<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Currency> */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'number' => (string) fake()->unique()->numberBetween(1000, 9999),
            'symbol' => null,
            'status' => CurrencyStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => CurrencyStatus::Inactive]);
    }
}
