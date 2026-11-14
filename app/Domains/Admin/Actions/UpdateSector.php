<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Sector;
use App\Support\I18n\LocaleResolver;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateSector
{
    public function __construct(
        private readonly LocaleResolver $locales,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(Sector $sector, array $data): Sector
    {
        return DB::transaction(function () use ($sector, $data): Sector {
            unset($data['slug']);

            /** @var array<string, string|null> $names */
            $names = is_array($data['name'] ?? null) ? $data['name'] : $sector->getTranslations('name');
            $defaultName = (string) ($names[$this->locales->default()] ?? $sector->getTranslation('name', $this->locales->default()));
            $previousDefault = (string) $sector->getTranslation('name', $this->locales->default());

            if ($sector->slug === '' || $defaultName !== $previousDefault) {
                $data['slug'] = UniqueSlug::from(
                    $defaultName,
                    fn (string $slug): bool => Sector::withTrashed()
                        ->whereKeyNot($sector->id)
                        ->where('slug', $slug)
                        ->exists(),
                );
            }

            $sector->update($data);

            return $sector->refresh();
        });
    }
}
