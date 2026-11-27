<?php

namespace Database\Factories;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ProductImage> */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

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
            'image' => function (array $attributes): string {
                $productId = $attributes['product_id'] ?? 0;
                $companyId = $attributes['company_id'] ?? 0;

                return sprintf(
                    'companies/%d/products/%d/images/%s.jpg',
                    $companyId,
                    $productId,
                    Str::uuid()->toString(),
                );
            },
            'featured' => false,
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

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'featured' => true,
        ]);
    }

    public function publiclyVisible(): static
    {
        return $this->state(fn (): array => [
            'public' => true,
        ]);
    }
}
