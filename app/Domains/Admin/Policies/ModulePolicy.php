<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Module;
use App\Models\User;

class ModulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('modules.viewAny');
    }

    public function view(User $user, Module $module): bool
    {
        return $user->can('modules.view');
    }

    public function create(User $user): bool
    {
        return $user->can('modules.create');
    }

    public function update(User $user, Module $module): bool
    {
        return $user->can('modules.update');
    }

    public function delete(User $user, Module $module): bool
    {
        return $user->can('modules.delete');
    }
}
