<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Enums\FamilyStatus;
use App\Domains\Catalog\Models\Family;
use App\Support\Strings\UniqueSlug;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;

class CreateFamily
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Family
    {
        return DB::transaction(function () use ($data): Family {
            $companyId = app(CurrentCompany::class)->id();

            // Unicidad solo entre no borrados (design D1); soft-deleted libera el slug.
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Family::query()
                    ->where('company_id', $companyId)
                    ->where('slug', $slug)
                    ->exists(),
            );
            $data['status'] ??= FamilyStatus::Inactive->value;

            return Family::query()->create($data);
        });
    }
}
