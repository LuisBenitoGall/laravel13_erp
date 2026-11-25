<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExchangeRate> */
class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'from_currency_id' => Currency::factory(),
            'to_currency_id' => Currency::factory(),
            'rate' => number_format(fake()->randomFloat(6, 0.1, 100), 6, '.', ''),
        ];
    }
}
