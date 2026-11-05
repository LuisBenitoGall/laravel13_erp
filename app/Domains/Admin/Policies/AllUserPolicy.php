<?php

namespace App\Domains\Admin\Policies;

use App\Models\User;

/**
 * Directorio global Todos los usuarios: `all-users.viewAny`.
 * Super Admin bypass vía Gate::before.
 */
class AllUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('all-users.viewAny');
    }
}
