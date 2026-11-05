<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Catálogo de permisos para la matriz UI de roles de plataforma.
 */
class PlatformRolePermissionCatalog
{
    /**
     * @return list<array{
     *     slug: string,
     *     label: string,
     *     level: string,
     *     color: string,
     *     icon: string,
     *     functionalities: list<array{
     *         slug: string,
     *         label: string,
     *         permissions: list<array{name: string, action: string}>
     *     }>
     * }>
     */
    public function forMatrix(bool $includeAdminOnly): array
    {
        $query = Module::query()
            ->where('status', ModuleStatus::Active)
            ->with(['functionalities' => fn ($q) => $q
                ->where('status', FunctionalityStatus::Active)
                ->orderByRaw('`sort` is null')
                ->orderBy('sort')
                ->orderBy('slug')])
            ->orderByRaw('`sort` is null')
            ->orderBy('sort')
            ->orderBy('slug');

        if (! $includeAdminOnly) {
            $query->where('level', '!=', ModuleLevel::AdminOnly);
        }

        /** @var Collection<int, Module> $modules */
        $modules = $query->get();

        $slugs = $modules
            ->flatMap(fn (Module $module) => $module->functionalities->pluck('slug'))
            ->unique()
            ->values()
            ->all();

        $permissionsBySlug = $this->permissionsGroupedByFunctionalitySlug($slugs);

        $matrix = [];

        foreach ($modules as $module) {
            $functionalities = [];

            foreach ($module->functionalities as $functionality) {
                $permissions = $permissionsBySlug[$functionality->slug] ?? [];

                if ($permissions === []) {
                    continue;
                }

                $functionalities[] = [
                    'slug' => $functionality->slug,
                    'label' => (string) $functionality->label,
                    'permissions' => $permissions,
                ];
            }

            if ($functionalities === []) {
                continue;
            }

            $matrix[] = [
                'slug' => $module->slug,
                'label' => (string) $module->label,
                'level' => $module->level->value,
                'color' => (string) $module->color,
                'icon' => (string) $module->icon,
                'functionalities' => $functionalities,
            ];
        }

        return $matrix;
    }

    /**
     * Set visible de una card Functionality (canónicos existentes + custom).
     *
     * @return list<array{name: string, action: string}>
     */
    public function permissionsForFunctionality(string $functionalitySlug): array
    {
        return $this->permissionsGroupedByFunctionalitySlug([$functionalitySlug])[$functionalitySlug] ?? [];
    }

    /**
     * @return list<string>
     */
    public function permissionNamesForFunctionality(string $functionalitySlug): array
    {
        return array_column($this->permissionsForFunctionality($functionalitySlug), 'name');
    }

    /**
     * Filtra nombres de permiso: si no AdminOnly, quita los de módulos AdminOnly.
     *
     * @param  list<string>  $permissionNames
     * @return list<string>
     */
    public function filterPermissionNames(array $permissionNames, bool $allowAdminOnly): array
    {
        $permissionNames = array_values(array_unique($permissionNames));

        if ($allowAdminOnly) {
            return $permissionNames;
        }

        $adminOnlySlugs = Module::query()
            ->where('level', ModuleLevel::AdminOnly)
            ->with('functionalities:id,module_id,slug')
            ->get()
            ->flatMap(fn (Module $m) => $m->functionalities->pluck('slug'))
            ->all();

        return array_values(array_filter(
            $permissionNames,
            function (string $name) use ($adminOnlySlugs): bool {
                $parts = explode('.', $name);
                $slug = $parts[0];

                return ! in_array($slug, $adminOnlySlugs, true);
            },
        ));
    }

    /**
     * @param  list<string>  $functionalitySlugs
     * @return array<string, list<array{name: string, action: string}>>
     */
    private function permissionsGroupedByFunctionalitySlug(array $functionalitySlugs): array
    {
        if ($functionalitySlugs === []) {
            return [];
        }

        $names = Permission::query()
            ->where(function ($query) use ($functionalitySlugs): void {
                foreach ($functionalitySlugs as $slug) {
                    $query->orWhere(function ($inner) use ($slug): void {
                        $inner->where('name', 'like', $slug.'.%')
                            ->where('name', '!=', $slug.'.access');
                    });
                }
            })
            ->pluck('name')
            ->filter(fn (string $name): bool => ! str_ends_with($name, '.access'))
            ->values()
            ->all();

        $grouped = [];

        foreach ($functionalitySlugs as $slug) {
            $grouped[$slug] = [];
        }

        $bySlug = [];
        foreach ($names as $name) {
            $slug = CanonicalPermission::functionalitySlug($name);
            if ($slug === null || ! in_array($slug, $functionalitySlugs, true)) {
                continue;
            }
            $bySlug[$slug][] = $name;
        }

        foreach ($bySlug as $slug => $slugNames) {
            $grouped[$slug] = $this->orderPermissionsForFunctionality($slug, $slugNames);
        }

        return $grouped;
    }

    /**
     * @param  list<string>  $names
     * @return list<array{name: string, action: string}>
     */
    private function orderPermissionsForFunctionality(string $slug, array $names): array
    {
        $names = array_values(array_unique($names));
        $ordered = [];
        $seen = [];

        foreach (CanonicalPermission::ACTION_SUFFIXES as $action) {
            $name = CanonicalPermission::composeName($slug, $action);
            if (in_array($name, $names, true)) {
                $ordered[] = ['name' => $name, 'action' => $action];
                $seen[$name] = true;
            }
        }

        $custom = array_values(array_filter(
            $names,
            fn (string $name): bool => ! isset($seen[$name]),
        ));
        sort($custom);

        foreach ($custom as $name) {
            $ordered[] = [
                'name' => $name,
                'action' => Str::after($name, $slug.'.'),
            ];
        }

        return $ordered;
    }
}
