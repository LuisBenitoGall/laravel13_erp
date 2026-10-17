<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\PlatformRolePermissionCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class CreatePlatformRole
{
    public function __construct(
        private readonly PlatformRolePermissionCatalog $catalog,
        private readonly SyncPlatformRolePermissions $syncPermissions,
    ) {}

    /**
     * @param  array{name: string, description?: string|null, universal: bool, permissions?: list<string>}  $data
     */
    public function handle(array $data): Role
    {
        $name = $data['name'];

        if ($name === Role::SUPER_ADMIN) {
            throw ValidationException::withMessages([
                'name' => __('rol_super_admin_inmutable'),
            ]);
        }

        return DB::transaction(function () use ($data, $name) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId(null);

            try {
                if (Role::query()->platform()->where('name', $name)->exists()) {
                    throw ValidationException::withMessages([
                        'name' => __('rol_nombre_duplicado'),
                    ]);
                }

                /** @var Role $role */
                $role = Role::query()->create([
                    'name' => $name,
                    'guard_name' => 'web',
                    'universal' => (bool) $data['universal'],
                    'description' => $data['description'] ?? null,
                ]);

                $permissions = $this->catalog->filterPermissionNames(
                    $data['permissions'] ?? [],
                    allowAdminOnly: ! $role->universal,
                );

                $this->syncPermissions->handle($role, $permissions);

                $fresh = $role->fresh(['permissions']);

                return $fresh instanceof Role ? $fresh : $role;
            } finally {
                setPermissionsTeamId($previousTeamId);
            }
        });
    }
}
