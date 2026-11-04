<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Country;
use App\Models\User;

/** Policy de Country; Province y Town delegan en los mismos permisos `countries.*` (D3). */
class CountryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('countries.viewAny');
    }

    public function view(User $user, Country $country): bool
    {
        return $user->can('countries.view');
    }

    public function create(User $user): bool
    {
        return $user->can('countries.create');
    }

    public function update(User $user, Country $country): bool
    {
        return $user->can('countries.update');
    }

    public function delete(User $user, Country $country): bool
    {
        return $user->can('countries.delete');
    }
}
