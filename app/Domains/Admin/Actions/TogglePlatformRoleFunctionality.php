<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\PlatformRolePermissionCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class TogglePlatformRoleFunctionality
{
    public function __construct(
        private readonly PlatformRolePermissionCatalog $catalog,
        private readonly SyncPlatformRoleModuleAccess $syncModuleAccess,
    ) {}

    /**
     * @return array{permission_names: list<string>}
     */
    public function handle(Role $role, string $functionalitySlug, bool $enabled): array
    {
        $this->assertPlatformManaged($role);

        $functionality = Functionality::query()
            ->where('slug', $functionalitySlug)
            ->with('module:id,slug,level')
            ->first();

        if ($functionality === null) {
            throw ValidationException::withMessages([
                'functionality' => __('rol_funcionalidad_no_atribuible'),
            ]);
        }

        $permissionNames = $this->catalog->permissionNamesForFunctionality($functionalitySlug);

        $filtered = $this->catalog->filterPermissionNames(
            $permissionNames,
            allowAdminOnly: ! $role->universal,
        );

        if ($filtered === []) {
            throw ValidationException::withMessages([
                'functionality' => __('rol_funcionalidad_no_atribuible'),
            ]);
        }

        $moduleSlug = $functionality->module->slug;

        return DB::transaction(function () use ($role, $filtered, $enabled, $moduleSlug) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId(null);

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

    private function assertPlatformManaged(Role $role): void
    {
        $teamKey = (string) config('permission.column_names.team_foreign_key');

        if ($role->isSuperAdmin() || $role->getAttribute($teamKey) !== null) {
            throw ValidationException::withMessages([
                'role' => __('rol_super_admin_inmutable'),
            ]);
        }
    }
}
