<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Models\Account;
use Illuminate\Support\Facades\DB;

class ToggleAccountStatus
{
    public function handle(Account $account, ?AccountStatus $status = null): Account
    {
        return DB::transaction(function () use ($account, $status): Account {
            $account->update([
                'status' => $status ?? ($account->status === AccountStatus::Active
                    ? AccountStatus::Inactive
                    : AccountStatus::Active),
            ]);

            return $account->refresh();
        });
    }
}
