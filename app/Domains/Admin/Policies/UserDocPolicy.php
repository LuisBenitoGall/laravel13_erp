<?php

namespace App\Domains\Admin\Policies;

use App\Models\User;
use App\Models\UserDoc;
use Illuminate\Support\Facades\Gate;

/**
 * Documentos personales del User: misma puerta que update de perfil
 * (self, o updateCompanyUser sobre el owner). Sin Spatie nuevo.
 */
class UserDocPolicy
{
    public function upload(User $actor, User $owner): bool
    {
        return $this->canManageOwner($actor, $owner);
    }

    public function download(User $actor, UserDoc $doc): bool
    {
        return $this->canManageDoc($actor, $doc);
    }

    public function delete(User $actor, UserDoc $doc): bool
    {
        return $this->canManageDoc($actor, $doc);
    }

    private function canManageDoc(User $actor, UserDoc $doc): bool
    {
        $owner = $doc->user;

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
