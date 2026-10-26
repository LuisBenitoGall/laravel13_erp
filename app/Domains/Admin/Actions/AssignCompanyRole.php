<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Permission\PermissionRegistrar;

class AssignCompanyRole
{
    public function handle(User $user, Company $company, string $role): void
    {
        if (! $user->companies()->where('companies.id', $company->id)->exists()) {
            throw new InvalidArgumentException('User must belong to the company before assigning a role.');
        }

        app(EnsureCompanyDefaultRoles::class)->handle($company);

        $localRole = Role::query()
            ->forCompany($company->id)
            ->where('name', $role)
            ->first();

        if ($localRole === null) {
            throw new InvalidArgumentException('Role must be a local role of the company.');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($company->id);

        try {
            DB::transaction(function () use ($user, $role): void {
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');

                /** @var list<string> $currentNames */
                $currentNames = $user->roles->pluck('name')->all();

                foreach ($currentNames as $existingName) {
                    if ($existingName !== $role) {
                        $user->removeRole($existingName);
                    }
                }

                $user->unsetRelation('roles');

                if (! $user->hasRole($role)) {
                    $user->assignRole($role);
                }
            });
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}
