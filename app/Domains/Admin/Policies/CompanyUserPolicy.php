<?php

namespace App\Domains\Admin\Policies;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;

/**
 * Index Usuarios empresa: `company-users.*` + vínculo a CurrentCompany.
 * Super Admin bypass vía Gate::before.
 */
class CompanyUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->belongsToCurrentCompany($user)
            && $user->can('company-users.viewAny');
    }

    public function update(User $user, User $member): bool
    {
        return $this->belongsToCurrentCompany($user)
            && $this->memberBelongsToCurrentCompany($member)
            && $user->can('company-users.update');
    }

    public function delete(User $user, User $member): bool
    {
        return $this->belongsToCurrentCompany($user)
            && $this->memberBelongsToCurrentCompany($member)
            && $user->can('company-users.delete');
    }

    private function belongsToCurrentCompany(User $user): bool
    {
        $companyId = app(CurrentCompany::class)->id();

        if ($companyId === null) {
            return false;
        }

        return $user->companies()->where('companies.id', $companyId)->exists();
    }

    private function memberBelongsToCurrentCompany(User $member): bool
    {
        $companyId = app(CurrentCompany::class)->id();

        if ($companyId === null) {
            return false;
        }

        return $member->companies()->where('companies.id', $companyId)->exists();
    }
}
