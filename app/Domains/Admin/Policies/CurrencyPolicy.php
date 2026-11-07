<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\Currency;
use App\Models\User;

/**
 * Index: método `viewAny` → Spatie `.view`. Show: método `view` → Spatie `.viewAny`.
 */
class CurrencyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('currencies.view');
    }

    public function view(User $user, Currency $currency): bool
    {
        return $user->can('currencies.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('currencies.create');
    }

    public function update(User $user, Currency $currency): bool
    {
        return $user->can('currencies.update');
    }

    public function delete(User $user, Currency $currency): bool
    {
        return $user->can('currencies.delete');
    }
}
