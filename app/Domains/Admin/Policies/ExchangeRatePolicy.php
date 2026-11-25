<?php

namespace App\Domains\Admin\Policies;

use App\Domains\Admin\Models\ExchangeRate;
use App\Models\User;

/**
 * Index: método `viewAny` → Spatie `.view`. Show: método `view` → Spatie `.viewAny`.
 */
class ExchangeRatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('exchange-rates.view');
    }

    public function view(User $user, ExchangeRate $exchangeRate): bool
    {
        return $user->can('exchange-rates.viewAny');
    }

    public function create(User $user): bool
    {
        return $user->can('exchange-rates.create');
    }

    public function update(User $user, ExchangeRate $exchangeRate): bool
    {
        return $user->can('exchange-rates.update');
    }

    public function delete(User $user, ExchangeRate $exchangeRate): bool
    {
        return $user->can('exchange-rates.delete');
    }
}
