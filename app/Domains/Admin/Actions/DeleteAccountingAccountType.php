<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\AccountingAccountType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteAccountingAccountType
{
    public function handle(AccountingAccountType $type): void
    {
        if ($type->children()->exists()) {
            throw ValidationException::withMessages([
                'accounting_account_type' => __('grupo_contable_con_hijos'),
            ]);
        }

        DB::transaction(fn () => $type->delete());
    }
}
