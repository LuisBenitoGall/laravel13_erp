<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class DeleteCompanyRole
{
    public function handle(Company $company, Role $role): void
    {
        $teamKey = (string) config('permission.column_names.team_foreign_key');

        if ((int) $role->getAttribute($teamKey) !== (int) $company->id) {
            throw ValidationException::withMessages([
                'role' => __('rol_empresa_fuera_de_tenant'),
            ]);
        }

        if ($role->isLocalProtected()) {
            throw ValidationException::withMessages([
                'role' => __('rol_empresa_protegido_no_borrable'),
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($company->id);

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
