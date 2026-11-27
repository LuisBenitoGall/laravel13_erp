<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\FamilyStatus;
use App\Domains\Catalog\Models\Family;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Family> */
class FamilyFactory extends Factory
{
    protected $model = Family::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'company_id' => Company::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'status' => FamilyStatus::Inactive,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => FamilyStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => FamilyStatus::Inactive,
        ]);
    }
}
