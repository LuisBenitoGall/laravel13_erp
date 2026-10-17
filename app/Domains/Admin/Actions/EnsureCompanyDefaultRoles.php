<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Partners\Models\Company;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea roles Spatie `manager` y `guest` para la company (Teams / company_id).
 */
class EnsureCompanyDefaultRoles
{
    /**
     * @return array{manager: RoleContract, guest: RoleContract}
     */
    public function handle(Company $company): array
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($company->id);

        try {
            return [
                'manager' => Role::findOrCreate('manager'),
                'guest' => Role::findOrCreate('guest'),
            ];
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
