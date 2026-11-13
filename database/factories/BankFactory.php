<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\BankStatus;
use App\Domains\Admin\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Bank> */
class BankFactory extends Factory
{
    protected $model = Bank::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->company());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'tradename' => fake()->optional()->company(),
            'lei' => null,
            'eu_code' => 'ES'.fake()->unique()->numerify('####'),
            'supervisor_code' => fake()->optional()->numerify('####'),
            'swift' => null,
            'swift_source' => null,
            'swift_updated_at' => null,
            'status' => BankStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => BankStatus::Inactive]);
    }
}
