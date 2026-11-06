<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\AccountingAccountType;
use App\Models\User;

class AccountingAccountTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting-account-types.view');
    }

    public function view(User $user, AccountingAccountType $accountingAccountType): bool
    {
        return $user->can('accounting-account-types.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('accounting-account-types.create');
    }

    public function update(User $user, AccountingAccountType $accountingAccountType): bool
    {
        return $user->can('accounting-account-types.update');
    }

    public function delete(User $user, AccountingAccountType $accountingAccountType): bool
    {
        return $user->can('accounting-account-types.delete');
    }
}
