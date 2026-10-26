<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Role;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Spatie\Permission\PermissionRegistrar;

/**
 * CRUD roles de company: manager de CurrentCompany + `company-roles.*`.
 * Super Admin bypass vía Gate::before.
 */
class CompanyRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isCompanyManager($user) && $user->can('company-roles.viewAny');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->belongsToCurrentCompany($role)
            && $this->isCompanyManager($user)
            && $user->can('company-roles.view');
    }

    public function create(User $user): bool
    {
        return $this->isCompanyManager($user) && $user->can('company-roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->belongsToCurrentCompany($role)
            && $this->isCompanyManager($user)
            && $user->can('company-roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->belongsToCurrentCompany($role)
            && ! $role->isLocalProtected()
            && $this->isCompanyManager($user)
            && $user->can('company-roles.delete');
    }

    private function isCompanyManager(User $user): bool
    {
        $companyId = app(CurrentCompany::class)->id();

        if ($companyId === null) {
            return false;
        }

        if (! $user->companies()->where('companies.id', $companyId)->exists()) {
            return false;
        }

        $previous = getPermissionsTeamId();
        setPermissionsTeamId($companyId);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        try {
            return $user->hasRole(Role::MANAGER);
        } finally {
            setPermissionsTeamId($previous);
        }
    }

    private function belongsToCurrentCompany(Role $role): bool
    {
        $companyId = app(CurrentCompany::class)->id();
        $teamKey = (string) config('permission.column_names.team_foreign_key');

        return $companyId !== null
            && (int) $role->getAttribute($teamKey) === (int) $companyId;
    }
}
