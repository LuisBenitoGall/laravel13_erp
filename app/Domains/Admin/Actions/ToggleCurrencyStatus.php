<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\Currency;
use Illuminate\Support\Facades\DB;

class ToggleCurrencyStatus
{
    /** Sin `$status` alterna Active/Inactive; con `$status` lo fija. */
    public function handle(Currency $currency, ?CurrencyStatus $status = null): Currency
    {
        return DB::transaction(function () use ($currency, $status): Currency {
            $currency->update([
                'status' => $status ?? ($currency->status === CurrencyStatus::Active
                    ? CurrencyStatus::Inactive
                    : CurrencyStatus::Active),
            ]);

            return $currency->refresh();
        });
    }
}
