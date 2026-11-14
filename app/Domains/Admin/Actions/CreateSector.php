<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Sector;
use App\Support\I18n\LocaleResolver;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateSector
{
    public function __construct(
        private readonly LocaleResolver $locales,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Sector
    {
        return DB::transaction(function () use ($data): Sector {
            /** @var array<string, string|null> $names */
            $names = is_array($data['name'] ?? null) ? $data['name'] : [];
            $defaultName = (string) ($names[$this->locales->default()] ?? '');

            $data['slug'] = UniqueSlug::from(
                $defaultName,
                fn (string $slug): bool => Sector::withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= SectorStatus::Active->value;

            return Sector::query()->create($data);
        });
    }
}
