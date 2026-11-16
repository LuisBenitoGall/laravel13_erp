<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Preference;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;

/**
 * Favoritos personales: sin Spatie Functionality (D4/D6). Ownership + tenant.
 */
class PreferencePolicy
{
    public function create(User $user): bool
    {
        return app(CurrentCompany::class)->id() !== null;
    }

    public function delete(User $user, Preference $preference): bool
    {
        return (int) $preference->user_id === (int) $user->id
            && $preference->belongsToCurrentCompany();
    }
}
