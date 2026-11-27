<?php

namespace Database\Factories;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDoc;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ProductDoc> */
class ProductDocFactory extends Factory
{
    protected $model = ProductDoc::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'company_id' => function (array $attributes): int {
                $product = Product::query()->find($attributes['product_id'])
                    ?? Product::factory()->create();

                return (int) $product->company_id;
            },
            'path' => function (array $attributes): string {
                $productId = $attributes['product_id'] ?? 0;
                $companyId = $attributes['company_id'] ?? 0;

                return sprintf(
                    'companies/%d/products/%d/docs/%s.pdf',
                    $companyId,
                    $productId,
                    Str::uuid()->toString(),
                );
            },
            'original_name' => fake()->words(2, true).'.pdf',
            'mime' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1_000, 500_000),
            'public' => false,
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (): array => [
            'product_id' => $product->id,
            'company_id' => $product->company_id,
        ]);
    }

    public function publiclyVisible(): static
    {
        return $this->state(fn (): array => [
            'public' => true,
        ]);
    }
}
