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

            $product->update($data);

            return $product->refresh();
        });
    }
}
