<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Enums\TagStatus;
use App\Domains\Catalog\Models\Tag;
use App\Support\Strings\UniqueSlug;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;

class CreateTag
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Tag
    {
        return DB::transaction(function () use ($data): Tag {
            $companyId = app(CurrentCompany::class)->id();

            // Unicidad solo entre no borrados (mismo patrón Family D1).
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Tag::query()
                    ->where('company_id', $companyId)
                    ->where('slug', $slug)
                    ->exists(),
            );
            $data['status'] ??= TagStatus::Inactive->value;

            return Tag::query()->create($data);
        });
    }
}
