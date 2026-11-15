<?php

namespace Database\Factories;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Workplace> */
class WorkplaceFactory extends Factory
{
    protected $model = Workplace::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->company().' HQ';

        return [
            'company_id' => Company::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'address' => fake()->streetAddress(),
            'cp' => fake()->numerify('#####'),
            'town_id' => null,
            'featured' => false,
            'status' => WorkplaceStatus::Active,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'featured' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => WorkplaceStatus::Inactive,
        ]);
    }
}
