<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ProductImage;
use Illuminate\Support\Facades\DB;

class UpdateProductImagePublic
{
    public function handle(ProductImage $image, bool $public): ProductImage
    {
        return DB::transaction(function () use ($image, $public): ProductImage {
            $image->update(['public' => $public]);

            return $image->refresh();
        });
    }
}
