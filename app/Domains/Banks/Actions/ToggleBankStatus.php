<?php

namespace App\Domains\Banks\Actions;

use App\Domains\Banks\Enums\BankStatus;
use App\Domains\Banks\Models\Bank;
use Illuminate\Support\Facades\DB;

class ToggleBankStatus
{
    /** Sin `$status` alterna Active/Inactive; con `$status` lo fija. */
    public function handle(Bank $bank, ?BankStatus $status = null): Bank
    {
        return DB::transaction(function () use ($bank, $status): Bank {
            $bank->update([
                'status' => $status ?? ($bank->status === BankStatus::Active
                    ? BankStatus::Inactive
                    : BankStatus::Active),
            ]);

            return $bank->refresh();
        });
    }
}
