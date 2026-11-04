<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateProvince
{
    /** @param  array<string, mixed>  $data */
    public function handle(Country $country, array $data): Province
    {
        return DB::transaction(function () use ($country, $data): Province {
            $data['country_id'] = $country->id;
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Province::withTrashed()
                    ->where('country_id', $country->id)
                    ->where('slug', $slug)
                    ->exists(),
            );
            $data['status'] ??= GeographyStatus::Active->value;

            return Province::query()->create($data);
        });
    }
}
