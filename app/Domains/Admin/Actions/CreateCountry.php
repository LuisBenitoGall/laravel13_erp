<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Country;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateCountry
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Country
    {
        return DB::transaction(function () use ($data): Country {
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Country::withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= GeographyStatus::Active->value;

            return Country::query()->create($data);
        });
    }
}
