<?php

namespace App\Domains\Banks\Actions;

use App\Domains\Banks\Models\Bank;
use Illuminate\Support\Facades\DB;

class DeleteBank
{
    /** Soft delete; aún no hay BankAccount consumidores. */
    public function handle(Bank $bank): void
    {
        DB::transaction(fn () => $bank->delete());
    }
}
