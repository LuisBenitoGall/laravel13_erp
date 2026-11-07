<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Role;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Spatie\Permission\PermissionRegistrar;

/**
 * CRUD roles de company: `company-roles.*` + perímetro CurrentCompany.
 * Index (método `viewAny` → Spatie `.view`): perímetro + permiso; sin exigir manager.
 * Create/view/update: permiso Spatie sin exigir rol manager.
 * Delete: sigue manager + permiso. Detalle (`view` → Spatie `.viewAny`).
 * Update/view rechazan universales. Super Admin bypass vía Gate::before.
 */
class CompanyRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->userBelongsToCurrentCompany($user)
            && $user->can('company-roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return ! $role->universal
            && $this->belongsToCurrentCompany($role)
            && $this->userBelongsToCurrentCompany($user)
            && $user->can('company-roles.viewAny');
    }

    public function create(User $user): bool
    {
        return $this->userBelongsToCurrentCompany($user)
            && $user->can('company-roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return ! $role->universal
            && $this->belongsToCurrentCompany($role)
            && $this->userBelongsToCurrentCompany($user)
            && $user->can('company-roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return ! $role->universal
            && $this->belongsToCurrentCompany($role)
            && ! $role->isLocalProtected()
            && $this->isCompanyManager($user)
            && $user->can('company-roles.delete');
    }

    private function isCompanyManager(User $user): bool
    {
        if (! $this->userBelongsToCurrentCompany($user)) {
            return false;
        }

        $companyId = app(CurrentCompany::class)->id();
        $previous = getPermissionsTeamId();
        setPermissionsTeamId($companyId);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        try {
            return $user->hasRole(Role::MANAGER);
        } finally {
            setPermissionsTeamId($previous);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }

    private function userBelongsToCurrentCompany(User $user): bool
    {
        $companyId = app(CurrentCompany::class)->id();

        if ($companyId === null) {
            return false;
        }

        return $user->companies()->where('companies.id', $companyId)->exists();
    }

    private function belongsToCurrentCompany(Role $role): bool
    {
        $companyId = app(CurrentCompany::class)->id();
        $teamKey = (string) config('permission.column_names.team_foreign_key');

        return $companyId !== null
            && (int) $role->getAttribute($teamKey) === (int) $companyId;
    }
}
