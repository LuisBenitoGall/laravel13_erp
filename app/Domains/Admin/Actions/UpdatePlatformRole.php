<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\PlatformRolePermissionCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class UpdatePlatformRole
{
    public function __construct(
        private readonly PlatformRolePermissionCatalog $catalog,
        private readonly SyncPlatformRolePermissions $syncPermissions,
    ) {}

    /**
     * @param  array{name?: string, description?: string|null, universal?: bool, permissions?: list<string>}  $data
     */
    public function handle(Role $role, array $data): Role
    {
        if ($role->isSuperAdmin() || $role->getAttribute(config('permission.column_names.team_foreign_key')) !== null) {
            throw ValidationException::withMessages([
                'role' => __('rol_super_admin_inmutable'),
            ]);
        }

        return DB::transaction(function () use ($role, $data) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId(null);

            try {
                $nameImmutable = $role->isProtectedTemplate();

                if ($nameImmutable && isset($data['name']) && $data['name'] !== $role->name) {
                    throw ValidationException::withMessages([
                        'name' => __('rol_plantilla_nombre_inmutable'),
                    ]);
                }

                if (! $nameImmutable && isset($data['name']) && $data['name'] !== $role->name) {
                    if ($data['name'] === Role::SUPER_ADMIN) {
                        throw ValidationException::withMessages([
                            'name' => __('rol_super_admin_inmutable'),
                        ]);
                    }

                    if (Role::query()->platform()->where('name', $data['name'])->where('id', '!=', $role->id)->exists()) {
                        throw ValidationException::withMessages([
                            'name' => __('rol_nombre_duplicado'),
                        ]);
                    }

                    $role->name = $data['name'];
                }

                if (array_key_exists('description', $data)) {
                    $role->description = $data['description'];
                }

                if (array_key_exists('universal', $data) && ! $role->isProtectedTemplate()) {
                    $role->universal = (bool) $data['universal'];
                }

                $role->save();

                if (array_key_exists('permissions', $data)) {
                    if ($role->isManagerTemplate()) {
                        // Matriz solo lectura: ignorar cambios de permisos.
                    } else {
                        $permissions = $this->catalog->filterPermissionNames(
                            $data['permissions'],
                            allowAdminOnly: ! $role->universal,
                        );
                        $this->syncPermissions->handle($role, $permissions);
                    }
                }

                return $role->fresh(['permissions']) ?? $role;
            } finally {
                setPermissionsTeamId($previousTeamId);
            }
        });
    }
}
