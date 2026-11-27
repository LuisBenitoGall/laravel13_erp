<?php

namespace App\Domains\Calendar\Policies;

use App\Domains\Calendar\Models\DayType;
use App\Models\User;

class DayTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('day-types.view');
    }

    public function view(User $user, DayType $dayType): bool
    {
        return $user->can('day-types.viewAny') && $dayType->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('day-types.create');
    }

    public function update(User $user, DayType $dayType): bool
    {
        return $user->can('day-types.update') && $dayType->belongsToCurrentCompany();
    }

    public function delete(User $user, DayType $dayType): bool
    {
        return $user->can('day-types.delete') && $dayType->belongsToCurrentCompany();
    }
}
