<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class DeletePlatformRole
{
    public function handle(Role $role): void
    {
        if ($role->isSuperAdmin() || $role->getAttribute(config('permission.column_names.team_foreign_key')) !== null) {
            throw ValidationException::withMessages([
                'role' => __('rol_super_admin_inmutable'),
            ]);
        }

        if ($role->isProtectedTemplate()) {
            throw ValidationException::withMessages([
                'role' => __('rol_plantilla_no_borrable'),
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            if ($role->users()->count() > 0) {
                throw ValidationException::withMessages([
                    'role' => __('rol_con_usuarios_no_borrable'),
                ]);
            }

            DB::transaction(function () use ($role): void {
                $role->syncPermissions([]);
                $role->delete();
            });
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
