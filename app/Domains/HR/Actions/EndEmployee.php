<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Cierra un periodo `active` sin crear sucesor (fin de relación laboral). */
class EndEmployee
{
    public function handle(Employee $employee, ?string $endDate = null): Employee
    {
        return DB::transaction(function () use ($employee, $endDate): Employee {
            $employee = Employee::query()->lockForUpdate()->findOrFail($employee->id);

            if ($employee->status !== EmployeeStatus::Active) {
                throw ValidationException::withMessages([
                    'employee' => __('empleo_finalizar_solo_activo'),
                ]);
            }

            $employee->update([
                'status' => EmployeeStatus::Ended,
                'end_date' => $endDate !== null
                    ? CarbonImmutable::parse($endDate)->toDateString()
                    : ($employee->end_date ?? now()->toDateString()),
            ]);

            return $employee->refresh();
        });
    }
}
