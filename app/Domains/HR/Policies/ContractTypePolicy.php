<?php

namespace App\Domains\HR\Policies;

use App\Domains\HR\Models\ContractType;
use App\Models\User;

class ContractTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contract-types.view');
    }

    public function view(User $user, ContractType $contractType): bool
    {
        return $user->can('contract-types.viewAny') && $contractType->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('contract-types.create');
    }

    public function update(User $user, ContractType $contractType): bool
    {
        return $user->can('contract-types.update') && $contractType->belongsToCurrentCompany();
    }

    public function delete(User $user, ContractType $contractType): bool
    {
        return $user->can('contract-types.delete') && $contractType->belongsToCurrentCompany();
    }
}
