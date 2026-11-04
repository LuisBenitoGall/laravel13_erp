<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Province;
use App\Models\User;

/** Delega en la Functionality `countries` (D3). */
class ProvincePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('countries.viewAny');
    }

    public function view(User $user, Province $province): bool
    {
        return $user->can('countries.view');
    }

    public function create(User $user): bool
    {
        return $user->can('countries.create');
    }

    public function update(User $user, Province $province): bool
    {
        return $user->can('countries.update');
    }

    public function delete(User $user, Province $province): bool
    {
        return $user->can('countries.delete');
    }
}
