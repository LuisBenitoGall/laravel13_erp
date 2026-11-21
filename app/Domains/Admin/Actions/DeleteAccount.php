<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteAccount
{
    public function handle(Account $account): void
    {
        if ($account->is_default) {
            throw ValidationException::withMessages([
                'account' => __('cuenta_default_no_borrable'),
            ]);
        }

        DB::transaction(fn () => $account->delete());
    }
}
