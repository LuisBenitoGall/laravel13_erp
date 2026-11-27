<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            unset($data['status']);
            $data['status'] = ProductStatus::Inactive->value;

            return Product::query()->create($data);
        });
    }
}
