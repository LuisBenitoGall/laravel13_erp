<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use Illuminate\Support\Collection;

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
                ->orderBy('slug')])
            ->orderBy('slug');

        if (! $includeAdminOnly) {
            $query->where('level', '!=', ModuleLevel::AdminOnly);
        }

        /** @var Collection<int, Module> $modules */
        $modules = $query->get();

        $matrix = [];

        foreach ($modules as $module) {
            $functionalities = [];

            foreach ($module->functionalities as $functionality) {
                $permissions = [];
                foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $action) {
                    $permissions[] = [
                        'name' => "{$functionality->slug}.{$action}",
                        'action' => $action,
                    ];
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
                'functionalities' => $functionalities,
            ];
        }

        return $matrix;
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
}
