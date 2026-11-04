<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\UnitStatus;
use App\Domains\Admin\Models\Unit;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateUnit
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Unit
    {
        return DB::transaction(function () use ($data): Unit {
            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Unit::withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= UnitStatus::Active->value;

            return Unit::query()->create($data);
        });
    }
}
