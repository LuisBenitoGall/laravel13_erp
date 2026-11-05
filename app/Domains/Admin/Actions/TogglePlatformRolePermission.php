<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\PlatformRolePermissionCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class TogglePlatformRolePermission
{
    public function __construct(
        private readonly PlatformRolePermissionCatalog $catalog,
        private readonly SyncPlatformRoleModuleAccess $syncModuleAccess,
    ) {}

    /**
     * @return array{permission_names: list<string>}
     */
    public function handle(Role $role, string $permissionName, bool $enabled): array
    {
        $this->assertPlatformManaged($role);

        $filtered = $this->catalog->filterPermissionNames(
            [$permissionName],
            allowAdminOnly: ! $role->universal,
        );

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

        return DB::transaction(function () use ($role, $permissionName, $enabled, $moduleSlug) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId(null);

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
