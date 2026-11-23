<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Models\Employee;
use Illuminate\Support\Facades\DB;

/** Edita datos del periodo; `user_id` y `status` no cambian por aquí. */
class UpdateEmployee
{
    /** @param  array<string, mixed>  $data */
    public function handle(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data): Employee {
            unset($data['user_id'], $data['status'], $data['company_id']);

            $employee->update($data);

            return $employee->refresh();
        });
    }
}
