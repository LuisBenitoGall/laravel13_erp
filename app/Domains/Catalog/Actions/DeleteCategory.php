<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteCategory
{
    public function handle(Category $category): void
    {
        DB::transaction(function () use ($category): void {
            if ($category->hasActiveChildren()) {
                throw ValidationException::withMessages([
                    'category' => __('categoria_tiene_hijos'),
                ]);
            }

            $category->delete();
        });
    }
}
