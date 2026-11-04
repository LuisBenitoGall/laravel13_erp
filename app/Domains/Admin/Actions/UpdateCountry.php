<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Country;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateCountry
{
    /** @param  array<string, mixed>  $data */
    public function handle(Country $country, array $data): Country
    {
        return DB::transaction(function () use ($country, $data): Country {
            unset($data['slug']);

            if ($country->slug === null || $country->slug === '') {
                $data['slug'] = UniqueSlug::from(
                    (string) ($data['name'] ?? $country->name),
                    fn (string $slug): bool => Country::withTrashed()->where('slug', $slug)->exists(),
                );
            }

            $country->update($data);

            return $country->refresh();
        });
    }
}
