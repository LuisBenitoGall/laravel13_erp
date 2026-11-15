<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Workplace;
use App\Models\User;

class WorkplacePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('workplaces.view');
    }

    public function view(User $user, Workplace $workplace): bool
    {
        return $user->can('workplaces.viewAny') && $workplace->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('workplaces.create');
    }

    public function update(User $user, Workplace $workplace): bool
    {
        return $user->can('workplaces.update') && $workplace->belongsToCurrentCompany();
    }

    public function delete(User $user, Workplace $workplace): bool
    {
        return $user->can('workplaces.delete') && $workplace->belongsToCurrentCompany();
    }
}
