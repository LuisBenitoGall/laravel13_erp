<?php

namespace App\Domains\Admin\Policies;

use App\Models\User;

/**
 * Delegaciones (Company hija): permisos Spatie `delegations.*`.
 * El perímetro (hija de la matriz de sesión) lo impone DelegationPerimeter
 * en FormRequests/controllers: Gate::before de Super Admin no lo salta.
 */
class DelegationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('delegations.view');
    }

    public function create(User $user): bool
    {
        return $user->can('delegations.create');
    }

    public function update(User $user): bool
    {
        return $user->can('delegations.update');
    }

    public function delete(User $user): bool
    {
        return $user->can('delegations.delete');
    }
}
