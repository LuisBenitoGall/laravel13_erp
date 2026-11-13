<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\AssertsCompanyRoleTenantPerimeter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Edit company: solo descripción (matriz vía toggles; name inmutable).
 */
class UpdateCompanyRole
{
    use AssertsCompanyRoleTenantPerimeter;

    /**
     * @param  array{description?: string|null}  $data
     */
    public function handle(Company $company, Role $role, array $data): Role
    {
        $this->assertCompanyIsCurrent($company);
        $this->assertRoleBelongsToCompany($role, $company);

        if ($role->universal) {
            throw ValidationException::withMessages([
                'role' => __('rol_universal_no_editable'),
            ]);
        }

        return DB::transaction(function () use ($company, $role, $data) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId($company->id);

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
