<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Edit plataforma: solo descripción (matriz vía toggles; name/universal inmutables aquí).
 */
class UpdatePlatformRole
{
    /**
     * @param  array{description?: string|null}  $data
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
                if (array_key_exists('description', $data)) {
                    $role->description = $data['description'];
                }

                $role->save();

                return $role->fresh(['permissions']) ?? $role;
            } finally {
                setPermissionsTeamId($previousTeamId);
            }
        });
    }
}
