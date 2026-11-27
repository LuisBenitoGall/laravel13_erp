<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'company_id' => Company::factory(),
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'status' => CategoryStatus::Inactive,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => CategoryStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => CategoryStatus::Inactive,
        ]);
    }

    public function childOf(Category $parent): static
    {
        return $this->state(fn (): array => [
            'company_id' => $parent->company_id,
            'parent_id' => $parent->id,
        ]);
    }
}
