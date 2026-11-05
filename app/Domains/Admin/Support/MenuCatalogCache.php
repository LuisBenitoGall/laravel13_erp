<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Catálogo Active Modules+Functionalities (sin company/user). Invalidar en CUD.
 *
 * D6: payload plano (arrays) — serializable con cache.serializable_classes=false
 * (database/redis). No Eloquent ni enums en el valor cacheado.
 */
final class MenuCatalogCache
{
    /** v2: árbol de arrays; v1 cacheaba Eloquent y rompía al leer del store database. */
    public const KEY = 'menu.catalog.active.v2';

    /**
     * @return Collection<int, array{
     *     id: int,
     *     slug: string,
     *     label: array<string, string>,
     *     color: string,
     *     icon: string,
     *     level: string,
     *     functionalities: list<array{id: int, slug: string, label: array<string, string>}>
     * }>
     */
    public static function modules(): Collection
    {
        /** @var list<array{id: int, slug: string, label: array<string, string>, color: string, icon: string, level: string, functionalities: list<array{id: int, slug: string, label: array<string, string>}>}> $payload */
        $payload = Cache::rememberForever(self::KEY, function (): array {
            return Module::query()
                ->where('status', ModuleStatus::Active)
                ->with(['functionalities' => fn ($q) => $q
                    ->where('status', FunctionalityStatus::Active)
                    ->orderBy('slug')])
                ->orderBy('slug')
                ->get()
                ->map(fn (Module $module): array => self::serializeModule($module))
                ->values()
                ->all();
        });

        return collect($payload);
    }

    public static function forget(): void
    {
        Cache::forget(self::KEY);
        // Huérfanos del payload Eloquent (v1) bajo serializable_classes=false.
        Cache::forget('menu.catalog.active');
    }

    /**
     * @return array{
     *     id: int,
     *     slug: string,
     *     label: array<string, string>,
     *     color: string,
     *     icon: string,
     *     level: string,
     *     functionalities: list<array{id: int, slug: string, label: array<string, string>}>
     * }
     */
    private static function serializeModule(Module $module): array
    {
        /** @var list<array{id: int, slug: string, label: array<string, string>}> $functionalities */
        $functionalities = $module->functionalities
            ->map(fn (Functionality $functionality): array => [
                'id' => (int) $functionality->id,
                'slug' => (string) $functionality->slug,
                /** @var array<string, string> $label */
                'label' => $functionality->getTranslations('label'),
            ])
            ->values()
            ->all();

        /** @var array<string, string> $moduleLabel */
        $moduleLabel = $module->getTranslations('label');

        return [
            'id' => (int) $module->id,
            'slug' => (string) $module->slug,
            'label' => $moduleLabel,
            'color' => (string) $module->color,
            'icon' => (string) $module->icon,
            'level' => $module->level->value,
            'functionalities' => $functionalities,
        ];
    }
}
