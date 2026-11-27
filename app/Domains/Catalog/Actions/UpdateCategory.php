<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCategory
{
    /** @param  array<string, mixed>  $data */
    public function handle(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            unset($data['slug']);

            if (array_key_exists('parent_id', $data)) {
                $parentId = $data['parent_id'];

                if ($parentId === null || $parentId === '') {
                    $data['parent_id'] = null;
                } else {
                    $parent = Category::query()
                        ->whereKey((int) $parentId)
                        ->where('company_id', $category->company_id)
                        ->first();

                    if ($parent === null) {
                        throw ValidationException::withMessages([
                            'parent_id' => __('categoria_padre_invalido'),
                        ]);
                    }

                    if ($category->wouldCycleWithParent($parent)) {
                        throw ValidationException::withMessages([
                            'parent_id' => __('categoria_ciclo_rechazado'),
                        ]);
                    }

                    $data['parent_id'] = $parent->id;
                }
            }

            $category->update($data);

            return $category->refresh();
        });
    }
}
