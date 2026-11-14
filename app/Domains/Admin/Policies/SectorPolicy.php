<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Sector;
use App\Models\User;

class SectorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sectors.view');
    }

    public function view(User $user, Sector $sector): bool
    {
        return $user->can('sectors.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('sectors.create');
    }

    public function update(User $user, Sector $sector): bool
    {
        return $user->can('sectors.update');
    }

    public function delete(User $user, Sector $sector): bool
    {
        return $user->can('sectors.delete');
    }
}
