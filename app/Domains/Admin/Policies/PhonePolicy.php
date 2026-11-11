<?php

namespace App\Domains\Admin\Policies;

use App\Models\Phone;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Teléfonos del User: misma puerta que update de perfil de la ficha
 * (self, o updateCompanyUser sobre el owner). Sin Spatie nuevo.
 * Super Admin bypass vía Gate::before — el perímetro company se refuerza
 * en Actions/controller.
 */
class PhonePolicy
{
    public function create(User $actor, User $owner): bool
    {
        return $this->canManageOwner($actor, $owner);
    }

    public function update(User $actor, Phone $phone): bool
    {
        return $this->canManagePhone($actor, $phone);
    }

    public function delete(User $actor, Phone $phone): bool
    {
        return $this->canManagePhone($actor, $phone);
    }

    public function markPrimary(User $actor, Phone $phone): bool
    {
        return $this->canManagePhone($actor, $phone);
    }

    private function canManagePhone(User $actor, Phone $phone): bool
    {
        $owner = $phone->phoneable;

        if (! $owner instanceof User) {
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
