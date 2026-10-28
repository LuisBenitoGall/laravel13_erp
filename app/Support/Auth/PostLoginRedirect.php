<?php

namespace App\Support\Auth;

use App\Models\User;

/**
 * Destino post-login / selección de empresa (todos → dashboard).
 */
final class PostLoginRedirect
{
    public function route(User $user): string
    {
        return route('dashboard');
    }
}
