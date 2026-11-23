<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\HR\Enums\ContractTypeStatus;
use App\Domains\HR\Models\ContractType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ContractType> */
class ContractTypeFactory extends Factory
{
    protected $model = ContractType::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'company_id' => Company::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'duration' => null,
            'with_holidays' => fake()->boolean(),
            'status' => ContractTypeStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => ContractTypeStatus::Inactive,
        ]);
    }

    /** Tipo indefinido: duration null. */
    public function indefinite(): static
    {
        return $this->state(fn (): array => [
            'duration' => null,
        ]);
    }

    /** Tipo temporal con duración en días. */
    public function fixed(int $days = 365): static
    {
        return $this->state(fn (): array => [
            'duration' => $days,
        ]);
    }
}
