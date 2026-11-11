<?php

namespace App\Domains\Admin\Policies;

use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Support\Facades\Gate;

/**
 * Emails adicionales: misma puerta que update de perfil de la ficha
 * (self, o updateCompanyUser sobre el owner). Sin Spatie nuevo.
 * Super Admin bypass vía Gate::before — el perímetro company se refuerza
 * en Actions/controller (como UpdateUserProfile).
 */
class UserEmailPolicy
{
    public function create(User $actor, User $owner): bool
    {
        return $this->canManageOwner($actor, $owner);
    }

    public function delete(User $actor, UserEmail $email): bool
    {
        $owner = $email->user;

        if ($owner === null) {
            return false;
        }

        return $this->canManageOwner($actor, $owner);
    }

    private function canManageOwner(User $actor, User $owner): bool
    {
        if ($actor->id === $owner->id) {
            return true;
        }

        return Gate::forUser($actor)->allows('updateCompanyUser', $owner);
    }
}
