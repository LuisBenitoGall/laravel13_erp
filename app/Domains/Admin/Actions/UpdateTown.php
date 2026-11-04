<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Town;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateTown
{
    /** @param  array<string, mixed>  $data */
    public function handle(Town $town, array $data): Town
    {
        return DB::transaction(function () use ($town, $data): Town {
            unset($data['slug'], $data['province_id']);

            if ($town->slug === null || $town->slug === '') {
                $data['slug'] = UniqueSlug::from(
                    (string) ($data['name'] ?? $town->name),
                    fn (string $slug): bool => Town::withTrashed()
                        ->where('province_id', $town->province_id)
                        ->where('slug', $slug)
                        ->exists(),
                );
            }

            $town->update($data);

            return $town->refresh();
        });
    }
}
