<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Town;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateTown
{
    /** @param  array<string, mixed>  $data */
    public function handle(Province $province, array $data): Town
    {
        return DB::transaction(function () use ($province, $data): Town {
            $data['province_id'] = $province->id;
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Town::withTrashed()
                    ->where('province_id', $province->id)
                    ->where('slug', $slug)
                    ->exists(),
            );
            $data['status'] ??= GeographyStatus::Active->value;

            return Town::query()->create($data);
        });
    }
}
