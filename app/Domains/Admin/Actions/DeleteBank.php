<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Bank;
use Illuminate\Support\Facades\DB;

class DeleteBank
{
    /** Soft delete; aún no hay BankAccount consumidores. */
    public function handle(Bank $bank): void
    {
        DB::transaction(fn () => $bank->delete());
    }
}
