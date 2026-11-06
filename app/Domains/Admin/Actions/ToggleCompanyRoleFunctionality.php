<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\AssertsCompanyRoleTenantPerimeter;
use App\Domains\Admin\Support\CompanyRolePermissionCatalog;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ToggleCompanyRoleFunctionality
{
    use AssertsCompanyRoleTenantPerimeter;

    public function __construct(
        private readonly CompanyRolePermissionCatalog $catalog,
        private readonly SyncCompanyRoleModuleAccess $syncModuleAccess,
    ) {}

    /**
     * @return array{permission_names: list<string>}
     */
    public function handle(Company $company, Role $role, string $functionalitySlug, bool $enabled): array
    {
        $this->assertCompanyIsCurrent($company);
        $this->assertMutableCompanyRole($company, $role);

        $functionality = Functionality::query()
            ->where('slug', $functionalitySlug)
            ->with('module:id,slug')
            ->first();

        if ($functionality === null) {
            throw ValidationException::withMessages([
                'functionality' => __('rol_funcionalidad_no_atribuible'),
            ]);
        }

        $permissionNames = $this->catalog->permissionNamesForFunctionality($functionalitySlug);
        $filtered = $this->catalog->filterPermissionNames($company, $permissionNames);

        if ($filtered === []) {
            throw ValidationException::withMessages([
                'functionality' => __('rol_funcionalidad_no_atribuible'),
            ]);
        }

        $moduleSlug = $functionality->module->slug;

        return DB::transaction(function () use ($company, $role, $filtered, $enabled, $moduleSlug) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId($company->id);

            try {
                foreach ($filtered as $name) {
                    $permission = Permission::findByName($name);

                    if ($enabled) {
                        $role->givePermissionTo($permission);
                    } else {
                        $role->revokePermissionTo($permission);
                    }
                }

                $this->syncModuleAccess->handle($role, $moduleSlug);

                $role->load('permissions');

                /** @var list<string> $names */
                $names = $role->permissions->pluck('name')->values()->all();

                return ['permission_names' => $names];
            } finally {
                setPermissionsTeamId($previousTeamId);
            }
        });
    }

    private function assertMutableCompanyRole(Company $company, Role $role): void
    {
        $this->assertRoleBelongsToCompany($role, $company);

        if ($role->universal) {
            throw ValidationException::withMessages([
                'role' => __('rol_universal_no_editable'),
            ]);
        }

        if ($role->isLocalManager()) {
            throw ValidationException::withMessages([
                'role' => __('rol_empresa_manager_matriz_solo_lectura'),
            ]);
        }
    }
}
