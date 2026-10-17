<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Role;
use App\Models\User;

/**
 * CRUD roles de plataforma: permisos `roles.*`; Super Admin bypass vía Gate::before.
 */
class PlatformRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.viewAny');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->isPlatformManaged($role) && $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->isPlatformManaged($role) && $user->can('roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->isPlatformManaged($role)
            && ! $role->isProtectedTemplate()
            && $user->can('roles.delete');
    }

    private function isPlatformManaged(Role $role): bool
    {
        return $role->getAttribute(config('permission.column_names.team_foreign_key')) === null
            && ! $role->isSuperAdmin();
    }
}
