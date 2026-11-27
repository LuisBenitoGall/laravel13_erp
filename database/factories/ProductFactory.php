<?php

namespace Database\Factories;

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->unique()->words(3, true),
            'type' => ProductType::Physical,
            'status' => ProductStatus::Inactive,
        ];
    }

    public function physical(): static
    {
        return $this->state(fn (): array => [
            'type' => ProductType::Physical,
        ]);
    }

    public function service(): static
    {
        return $this->state(fn (): array => [
            'type' => ProductType::Service,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => ProductStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => ProductStatus::Inactive,
        ]);
    }
}
