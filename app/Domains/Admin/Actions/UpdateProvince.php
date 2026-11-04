<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Province;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateProvince
{
    /** @param  array<string, mixed>  $data */
    public function handle(Province $province, array $data): Province
    {
        return DB::transaction(function () use ($province, $data): Province {
            unset($data['slug'], $data['country_id']);

            if ($province->slug === null || $province->slug === '') {
                $data['slug'] = UniqueSlug::from(
                    (string) ($data['name'] ?? $province->name),
                    fn (string $slug): bool => Province::withTrashed()
                        ->where('country_id', $province->country_id)
                        ->where('slug', $slug)
                        ->exists(),
                );
            }

            $province->update($data);

            return $province->refresh();
        });
    }
}
