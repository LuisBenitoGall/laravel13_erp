<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ProductImage;
use Illuminate\Support\Facades\DB;

class MarkProductImageFeatured
{
    public function handle(ProductImage $image): ProductImage
    {
        return DB::transaction(function () use ($image): ProductImage {
            ProductImage::withTrashed()
                ->where('product_id', $image->product_id)
                ->whereKeyNot($image->id)
                ->where('featured', true)
                ->update(['featured' => false]);

            $image->update(['featured' => true]);

            return $image->refresh();
        });
    }
}
