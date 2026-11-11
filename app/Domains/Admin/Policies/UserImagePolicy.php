<?php

namespace App\Domains\Admin\Policies;

use App\Models\User;
use App\Models\UserImage;
use Illuminate\Support\Facades\Gate;

/**
 * Imágenes del User: misma puerta que update de perfil de la ficha
 * (self, o updateCompanyUser sobre el owner). Sin Spatie nuevo.
 */
class UserImagePolicy
{
    public function upload(User $actor, User $owner): bool
    {
        return $this->canManageOwner($actor, $owner);
    }

    public function delete(User $actor, UserImage $image): bool
    {
        return $this->canManageImage($actor, $image);
    }

    public function markFeatured(User $actor, UserImage $image): bool
    {
        return $this->canManageImage($actor, $image);
    }

    public function updatePublic(User $actor, UserImage $image): bool
    {
        return $this->canManageImage($actor, $image);
    }

    private function canManageImage(User $actor, UserImage $image): bool
    {
        $owner = $image->user;

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
