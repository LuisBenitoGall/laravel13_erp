<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Partners\Models\Company;
use App\Models\User;
use InvalidArgumentException;
use Spatie\Permission\PermissionRegistrar;

class AssignCompanyRole
{
    public function handle(User $user, Company $company, string $role): void
    {
        if ($role !== 'manager' && $role !== 'guest') {
            throw new InvalidArgumentException('Only manager|guest company roles may be assigned.');
        }

        if (! $user->companies()->where('companies.id', $company->id)->exists()) {
            throw new InvalidArgumentException('User must belong to the company before assigning a role.');
        }

        app(EnsureCompanyDefaultRoles::class)->handle($company);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($company->id);

        try {
            if ($user->hasRole('manager') && $role !== 'manager') {
                $user->removeRole('manager');
            }
            if ($user->hasRole('guest') && $role !== 'guest') {
                $user->removeRole('guest');
            }

            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
