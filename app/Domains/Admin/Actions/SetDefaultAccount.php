<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Account;
use Illuminate\Support\Facades\DB;

class SetDefaultAccount
{
    public function handle(Account $account): Account
    {
        return DB::transaction(function () use ($account): Account {
            Account::query()
                ->where('is_default', true)
                ->whereKeyNot($account->id)
                ->update(['is_default' => false]);

            $account->forceFill(['is_default' => true])->save();

            return $account->refresh();
        });
    }
}
