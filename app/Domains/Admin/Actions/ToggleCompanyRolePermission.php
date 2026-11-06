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

class ToggleCompanyRolePermission
{
    use AssertsCompanyRoleTenantPerimeter;

    public function __construct(
        private readonly CompanyRolePermissionCatalog $catalog,
        private readonly SyncCompanyRoleModuleAccess $syncModuleAccess,
    ) {}

    /**
     * @return array{permission_names: list<string>}
     */
    public function handle(Company $company, Role $role, string $permissionName, bool $enabled): array
    {
        $this->assertCompanyIsCurrent($company);
        $this->assertMutableCompanyRole($company, $role);

        $filtered = $this->catalog->filterPermissionNames($company, [$permissionName]);

        if ($filtered === []) {
            throw ValidationException::withMessages([
                'permission' => __('rol_permiso_no_atribuible'),
            ]);
        }

        $moduleSlug = $this->moduleSlugForPermission($permissionName);

        if ($moduleSlug === null) {
            throw ValidationException::withMessages([
                'permission' => __('rol_permiso_no_atribuible'),
            ]);
        }

        return DB::transaction(function () use ($company, $role, $permissionName, $enabled, $moduleSlug) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId($company->id);

            try {
                Permission::findOrCreate($permissionName);

                if ($enabled) {
                    $role->givePermissionTo($permissionName);
                } else {
                    $role->revokePermissionTo($permissionName);
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

    private function moduleSlugForPermission(string $permissionName): ?string
    {
        $parts = explode('.', $permissionName, 2);

        if (count($parts) !== 2 || $parts[1] === 'access') {
            return null;
        }

        $functionality = Functionality::query()
            ->where('slug', $parts[0])
            ->with('module:id,slug')
            ->first();

        return $functionality?->module?->slug;
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
