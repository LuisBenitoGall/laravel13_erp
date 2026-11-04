<?php

namespace Database\Factories;

use App\Domains\Admin\Models\AccountingAccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AccountingAccountType> */
class AccountingAccountTypeFactory extends Factory
{
    protected $model = AccountingAccountType::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'autoreference' => 0,
            'code' => (string) fake()->unique()->numberBetween(1, 9),
            'name' => fake()->unique()->words(3, true),
            'mode' => null,
        ];
    }
}
