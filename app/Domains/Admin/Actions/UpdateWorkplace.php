<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Workplace;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateWorkplace
{
    /** @param  array<string, mixed>  $data */
    public function handle(Workplace $workplace, array $data): Workplace
    {
        return DB::transaction(function () use ($workplace, $data): Workplace {
            unset($data['slug']);

            if (array_key_exists('name', $data) && (string) $data['name'] !== $workplace->name) {
                $data['slug'] = UniqueSlug::from(
                    (string) $data['name'],
                    fn (string $slug): bool => Workplace::withTrashed()
                        ->where('slug', $slug)
                        ->whereKeyNot($workplace->id)
                        ->exists(),
                );
            }

            if (array_key_exists('featured', $data) && (bool) $data['featured'] === true) {
                Workplace::query()
                    ->where('featured', true)
                    ->whereKeyNot($workplace->id)
                    ->update(['featured' => false]);
            }

            $workplace->update($data);

            return $workplace->refresh();
        });
    }
}
