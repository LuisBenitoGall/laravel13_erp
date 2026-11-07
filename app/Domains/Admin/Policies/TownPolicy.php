<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Town;
use App\Models\User;

/** Delega en la Functionality `countries` (D3). */
class TownPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('countries.view');
    }

    public function view(User $user, Town $town): bool
    {
        return $user->can('countries.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('countries.create');
    }

    public function update(User $user, Town $town): bool
    {
        return $user->can('countries.update');
    }

    public function delete(User $user, Town $town): bool
    {
        return $user->can('countries.delete');
    }
}
