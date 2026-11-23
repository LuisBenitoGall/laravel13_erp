<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\Employee;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Renovar: cierra el periodo `active` (→ ended, indefinido con fin D−1) y crea
 * el nuevo en la misma transacción. No basta con mutar el tipo sobre la misma fila.
 */
class RenewEmployee
{
    public function __construct(
        private readonly CreateEmployee $createEmployee,
        private readonly AssertEmployeeNoOverlap $assertNoOverlap,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(Employee $current, array $data): Employee
    {
        return DB::transaction(function () use ($current, $data): Employee {
            User::query()->whereKey($current->user_id)->lockForUpdate()->first();

            $current = Employee::query()->lockForUpdate()->findOrFail($current->id);

            if ($current->status !== EmployeeStatus::Active) {
                throw ValidationException::withMessages([
                    'employee' => __('empleo_renovar_solo_activo'),
                ]);
            }

            $newStart = CarbonImmutable::parse((string) $data['start_date'])->startOfDay();
            $newEnd = ! empty($data['end_date'])
                ? CarbonImmutable::parse((string) $data['end_date'])->startOfDay()
                : null;
            $currentStart = CarbonImmutable::parse($current->start_date->toDateString())->startOfDay();

            // Excluir el current que se cierra; scheduled/otros actives sí bloquean.
            $this->assertNoOverlap->handle(
                (int) $current->user_id,
                (int) $current->company_id,
                $newStart,
                $newEnd,
                [(int) $current->id],
            );

            $closing = $newStart->subDay();
            // Indefinido: siempre D−1 (no conservar null). Con fin: conservar.
            $endDate = $current->end_date ?? ($closing->lessThan($currentStart) ? $currentStart : $closing);

            $current->update([
                'status' => EmployeeStatus::Ended,
                'end_date' => $endDate,
            ]);

            return $this->createEmployee->handle([
                ...$data,
                'user_id' => $current->user_id,
                'company_id' => $current->company_id,
            ]);
        });
    }
}
