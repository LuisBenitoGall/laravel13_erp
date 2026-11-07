<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Matriz tenant: módulos autorizados ∩ activos ∩ ¬AdminOnly;
 * permisos = canónicos existentes en BD + custom del slug (patrón plataforma).
 */
class CompanyRolePermissionCatalog
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
    public function forCompany(Company $company): array
    {
        $moduleIds = $this->eligibleModuleIds($company);

        if ($moduleIds === []) {
            return [];
        }

        /** @var Collection<int, Module> $modules */
        $modules = Module::query()
            ->whereIn('id', $moduleIds)
            ->where('status', ModuleStatus::Active)
            ->where('level', '!=', ModuleLevel::AdminOnly)
            ->with(['functionalities' => fn ($q) => $q
                ->where('status', FunctionalityStatus::Active)
                ->orderByRaw('`sort` is null')
                ->orderBy('sort')
                ->orderBy('slug')])
            ->orderByRaw('`sort` is null')
            ->orderBy('sort')
            ->orderBy('slug')
            ->get();

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
     * @return list<string>
     */
    public function allowedPermissionNames(Company $company): array
    {
        $names = [];

        foreach ($this->forCompany($company) as $module) {
            foreach ($module['functionalities'] as $functionality) {
                foreach ($functionality['permissions'] as $permission) {
                    $names[] = $permission['name'];
                }
            }
        }

        return $names;
    }

    /**
     * @param  list<string>  $permissionNames
     * @return list<string>
     */
    public function filterPermissionNames(Company $company, array $permissionNames): array
    {
        $allowed = array_flip($this->allowedPermissionNames($company));

        return array_values(array_filter(
            array_unique($permissionNames),
            fn (string $name): bool => isset($allowed[$name]),
        ));
    }

    /** @return list<int> */
    private function eligibleModuleIds(Company $company): array
    {
        $authorizedIds = CompanyModuleAuthorization::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->pluck('module_id');

        $enabledIds = CompanyModule::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->pluck('module_id');

        return $authorizedIds->intersect($enabledIds)->values()->all();
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
