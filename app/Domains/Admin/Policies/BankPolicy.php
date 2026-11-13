<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Bank;
use App\Models\User;

class BankPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('banks.view');
    }

    public function view(User $user, Bank $bank): bool
    {
        return $user->can('banks.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('banks.create');
    }

    public function update(User $user, Bank $bank): bool
    {
        return $user->can('banks.update');
    }

    public function delete(User $user, Bank $bank): bool
    {
        return $user->can('banks.delete');
    }
}
