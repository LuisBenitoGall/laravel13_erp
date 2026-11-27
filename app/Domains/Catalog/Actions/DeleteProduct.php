<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;

class DeleteProduct
{
    public function handle(Product $product): void
    {
        DB::transaction(fn () => $product->delete());
    }
}
