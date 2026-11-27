<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Models\Category;
use App\Support\Strings\UniqueSlug;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCategory
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            $companyId = app(CurrentCompany::class)->id();

            $parentId = $data['parent_id'] ?? null;
            if ($parentId !== null && $parentId !== '') {
                $parent = Category::query()
                    ->whereKey((int) $parentId)
                    ->where('company_id', $companyId)
                    ->first();

                if ($parent === null) {
                    throw ValidationException::withMessages([
                        'parent_id' => __('categoria_padre_invalido'),
                    ]);
                }

                $data['parent_id'] = $parent->id;
            } else {
                $data['parent_id'] = null;
            }

            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Category::query()
                    ->where('company_id', $companyId)
                    ->where('slug', $slug)
                    ->exists(),
            );
            $data['status'] ??= CategoryStatus::Inactive->value;

            return Category::query()->create($data);
        });
    }
}
