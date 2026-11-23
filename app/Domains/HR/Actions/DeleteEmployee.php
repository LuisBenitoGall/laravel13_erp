<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Models\Employee;
use Illuminate\Support\Facades\DB;

class DeleteEmployee
{
    public function handle(Employee $employee): void
    {
        DB::transaction(fn () => $employee->delete());
    }
}
