<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDoc;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadProductDocs
{
    /**
     * @param  list<UploadedFile>  $files
     * @return list<ProductDoc>
     */
    public function handle(Product $product, array $files): array
    {
        $maxPerProduct = (int) config('product_docs.max_per_product', 30);
        $maxPerUpload = (int) config('product_docs.max_per_upload', 10);

        if (count($files) > $maxPerUpload) {
            throw ValidationException::withMessages([
                'docs' => __('docs_limite_subida', ['max' => $maxPerUpload]),
            ]);
        }

        $current = $product->docs()->count();
        if ($current + count($files) > $maxPerProduct) {
            throw ValidationException::withMessages([
                'docs' => __('docs_limite_producto', ['max' => $maxPerProduct]),
            ]);
        }

        return DB::transaction(function () use ($product, $files): array {
            $created = [];

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
                $path = sprintf(
                    'companies/%d/products/%d/docs/%s.%s',
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

                $created[] = ProductDoc::query()->create([
                    'product_id' => $product->id,
                    'company_id' => $product->company_id,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName() ?: basename($path),
                    'mime' => $file->getClientMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize() ?: 0,
                    'public' => false,
                ]);
            }

            return $created;
        });
    }
}
