<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\AssertsCompanyRoleTenantPerimeter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class DeleteCompanyRole
{
    use AssertsCompanyRoleTenantPerimeter;

    public function handle(Company $company, Role $role): void
    {
        $this->assertCompanyIsCurrent($company);
        $this->assertRoleBelongsToCompany($role, $company);

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
