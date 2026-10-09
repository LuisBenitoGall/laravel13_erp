<?php

namespace Database\Factories;

use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Company> */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'tradename' => $name,
            'nif' => strtoupper(fake()->bothify('?########?')),
            'status' => CompanyStatus::Active,
            'logo' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => CompanyStatus::Draft,
        ]);
    }
}
