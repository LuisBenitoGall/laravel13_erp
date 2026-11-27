<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteProductImage
{
    public function handle(ProductImage $image): void
    {
        DB::transaction(function () use ($image): void {
            $path = $image->image;

            if ($image->featured) {
                $image->update(['featured' => false]);
            }

            $image->delete();

            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        });
    }
}
