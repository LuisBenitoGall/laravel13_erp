<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Workplace;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateWorkplace
{
    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Workplace
    {
        return DB::transaction(function () use ($data): Workplace {
            $isFirst = Workplace::query()->doesntExist();

            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Workplace::withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= WorkplaceStatus::Active->value;
            $data['featured'] = $isFirst
                ? true
                : (bool) ($data['featured'] ?? false);

            if ($data['featured']) {
                Workplace::query()->where('featured', true)->update(['featured' => false]);
            }

            return Workplace::query()->create($data);
        });
    }
}
