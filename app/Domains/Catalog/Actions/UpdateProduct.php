<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateProduct
{
    /** @param  array<string, mixed>  $data */
    public function handle(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            unset($data['type']);

            $syncCategories = array_key_exists('category_ids', $data);
            $syncTags = array_key_exists('tag_ids', $data);

            $categoryIds = $syncCategories
                ? array_map('intval', $data['category_ids'] ?? [])
                : null;
            $tagIds = $syncTags
                ? array_map('intval', $data['tag_ids'] ?? [])
                : null;

            unset($data['category_ids'], $data['tag_ids']);

            if (array_key_exists('family_id', $data) && $data['family_id'] === '') {
                $data['family_id'] = null;
            }

            $product->update($data);

            if ($syncCategories) {
                $product->categories()->sync($categoryIds ?? []);
            }

            if ($syncTags) {
                $product->tags()->sync($tagIds ?? []);
            }

            return $product->refresh();
        });
    }
}
