<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadProductImages
{
    /**
     * @param  list<UploadedFile>  $files
     * @return list<ProductImage>
     */
    public function handle(Product $product, array $files): array
    {
        $maxPerProduct = (int) config('product_images.max_per_product', 20);
        $maxPerUpload = (int) config('product_images.max_per_upload', 20);

        if (count($files) > $maxPerUpload) {
            throw ValidationException::withMessages([
                'images' => __('imagenes_limite_subida', ['max' => $maxPerUpload]),
            ]);
        }

        $current = $product->images()->count();
        if ($current + count($files) > $maxPerProduct) {
            throw ValidationException::withMessages([
                'images' => __('imagenes_limite_producto', ['max' => $maxPerProduct]),
            ]);
        }

        return DB::transaction(function () use ($product, $files): array {
            $created = [];

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
                $path = sprintf(
                    'companies/%d/products/%d/images/%s.%s',
                    $product->company_id,
                    $product->id,
                    Str::uuid()->toString(),
                    $ext,
                );
                Storage::disk('local')->putFileAs(
                    dirname($path),
                    $file,
                    basename($path),
                );

                $created[] = ProductImage::query()->create([
                    'product_id' => $product->id,
                    'company_id' => $product->company_id,
                    'image' => $path,
                    'featured' => false,
                    'public' => false,
                ]);
            }

            return $created;
        });
    }
}
