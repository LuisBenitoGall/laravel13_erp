<?php

namespace App\Support\Auth;

use App\Models\User;

/**
 * Destino post-login / selección de empresa.
 * Super Admin → catálogo Admin; resto → dashboard (evita AdminOnly).
 */
final class PostLoginRedirect
{
    public function route(User $user): string
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);

        try {
            if ($user->hasRole('Super Admin')) {
                return route('admin.modules.index');
            }
        } finally {
            setPermissionsTeamId($previousTeamId);
        }

        return route('dashboard');
    }
}
