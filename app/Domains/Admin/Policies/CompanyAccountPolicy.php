<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\CompanyAccount;
use App\Models\User;

class CompanyAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('profile.view');
    }

    public function view(User $user, CompanyAccount $companyAccount): bool
    {
        return $user->can('profile.viewAny') && $companyAccount->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('profile.update');
    }

    public function update(User $user, CompanyAccount $companyAccount): bool
    {
        return $user->can('profile.update') && $companyAccount->belongsToCurrentCompany();
    }

    public function delete(User $user, CompanyAccount $companyAccount): bool
    {
        return false;
    }
}
