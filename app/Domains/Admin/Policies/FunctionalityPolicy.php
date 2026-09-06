<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Functionality;
use App\Models\User;

class FunctionalityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('functionalities.viewAny');
    }

    public function view(User $user, Functionality $functionality): bool
    {
        return $user->can('functionalities.view');
    }

    public function create(User $user): bool
    {
        return $user->can('functionalities.create');
    }

    public function update(User $user, Functionality $functionality): bool
    {
        return $user->can('functionalities.update');
    }

    public function delete(User $user, Functionality $functionality): bool
    {
        return $user->can('functionalities.delete');
    }
}
