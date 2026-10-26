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

/**
 * Matriz de permisos tenant: módulos autorizados ∩ activos ∩ ¬AdminOnly.
 */
class CompanyRolePermissionCatalog
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
                ->orderBy('slug')])
            ->orderBy('slug')
            ->get();

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
}
