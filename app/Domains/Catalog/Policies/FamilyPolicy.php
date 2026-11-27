<?php

namespace App\Domains\Catalog\Policies;

use App\Domains\Catalog\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('families.view');
    }

    public function view(User $user, Family $family): bool
    {
        return $user->can('families.viewAny') && $family->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('families.create');
    }

    public function update(User $user, Family $family): bool
    {
        return $user->can('families.update') && $family->belongsToCurrentCompany();
    }

    public function delete(User $user, Family $family): bool
    {
        return $user->can('families.delete') && $family->belongsToCurrentCompany();
    }
}
