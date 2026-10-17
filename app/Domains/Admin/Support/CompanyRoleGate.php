<?php

namespace App\Domains\Admin\Support;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/** Quién puede asignar manager|guest en una company. */
final class CompanyRoleGate
{
    public static function canAssign(User $user, Company $company): bool
    {
        $previous = getPermissionsTeamId();

        try {
            setPermissionsTeamId(null);
            self::flushRoleCache($user);

            if ($user->hasRole('Super Admin')) {
                return true;
            }

            if (! $user->companies()->where('companies.id', $company->id)->exists()) {
                return false;
            }

            setPermissionsTeamId($company->id);
            self::flushRoleCache($user);

            return $user->hasRole('manager');
        } finally {
            setPermissionsTeamId($previous);
        }
    }

    private static function flushRoleCache(User $user): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');
    }
}
