<?php

namespace App\Domains\Admin\Policies;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Support\Facades\Gate;

/**
 * Membresías user_companies en ficha: self o updateCompanyUser sobre el owner.
 */
class UserCompanyPolicy
{
    public function viewAny(User $actor, User $owner): bool
    {
        return $this->canManageOwner($actor, $owner);
    }

    public function update(User $actor, UserCompany $membership): bool
    {
        return $this->canManageMembership($actor, $membership);
    }

    public function delete(User $actor, UserCompany $membership): bool
    {
        return $this->canManageMembership($actor, $membership);
    }

    private function canManageMembership(User $actor, UserCompany $membership): bool
    {
        $owner = $membership->user;

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
