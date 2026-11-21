<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounts.view');
    }

    public function view(User $user, Account $account): bool
    {
        return $user->can('accounts.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('accounts.create');
    }

    public function update(User $user, Account $account): bool
    {
        return $user->can('accounts.update');
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->can('accounts.delete');
    }
}
