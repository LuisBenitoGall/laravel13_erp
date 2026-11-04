<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Unit;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateUnit
{
    /** @param  array<string, mixed>  $data */
    public function handle(Unit $unit, array $data): Unit
    {
        return DB::transaction(function () use ($unit, $data): Unit {
            unset($data['slug']);

            $name = $data['name'] ?? $unit->name;

            if ($unit->slug === null || $unit->slug === '' || $name !== $unit->name) {
                $data['slug'] = UniqueSlug::from(
                    (string) $name,
                    fn (string $slug): bool => Unit::withTrashed()
                        ->whereKeyNot($unit->id)
                        ->where('slug', $slug)
                        ->exists(),
                );
            }

            $unit->update($data);

            return $unit->refresh();
        });
    }
}
