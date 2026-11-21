<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Account> */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'rate' => fake()->randomFloat(2, 0, 9999),
            'duration' => fake()->optional()->numberBetween(30, 365),
            'status' => AccountStatus::Active,
            'is_default' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => AccountStatus::Inactive]);
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
