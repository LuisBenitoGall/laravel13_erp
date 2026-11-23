<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\Employee;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

/**
 * Opción B: el nuevo intervalo no puede solapar con ningún periodo
 * `scheduled`/`active` del mismo user+company (fin null = abierto; `ended` fuera).
 * `exceptIds`: actives que esta operación cierra (Create→active / Renew current).
 */
class AssertEmployeeNoOverlap
{
    /**
     * @param  list<int>  $exceptIds
     */
    public function handle(
        int $userId,
        int $companyId,
        CarbonInterface $newStart,
        ?CarbonInterface $newEnd = null,
        array $exceptIds = [],
    ): void {
        $newStartDay = CarbonImmutable::parse($newStart->toDateString())->startOfDay();
        $newEndDay = $newEnd !== null
            ? CarbonImmutable::parse($newEnd->toDateString())->startOfDay()
            : null;

        $candidates = Employee::query()
            ->withoutGlobalScope('company')
            ->where('user_id', $userId)
            ->where('company_id', $companyId)
            ->whereIn('status', [EmployeeStatus::Scheduled, EmployeeStatus::Active])
            ->when(
                $exceptIds !== [],
                fn ($query) => $query->whereKeyNot($exceptIds),
            )
            ->get(['id', 'start_date', 'end_date', 'status']);

        foreach ($candidates as $existing) {
            $existingStart = CarbonImmutable::parse($existing->start_date->toDateString())->startOfDay();
            $existingEnd = $existing->end_date !== null
                ? CarbonImmutable::parse($existing->end_date->toDateString())->startOfDay()
                : null;

            if (! $this->intervalsOverlap($newStartDay, $newEndDay, $existingStart, $existingEnd)) {
                continue;
            }

            $reference = $existingEnd?->toDateString() ?? $existingStart->toDateString();

            throw ValidationException::withMessages([
                'start_date' => __('empleo_solape_con_activo', [
                    'date' => $reference,
                ]),
            ]);
        }
    }

    /**
     * Intervalos [start, end] inclusivos; end null = abierto (+∞).
     * No solapan si el nuevo empieza después del fin del otro, o termina
     * antes del inicio del otro.
     */
    private function intervalsOverlap(
        CarbonImmutable $newStart,
        ?CarbonImmutable $newEnd,
        CarbonImmutable $existingStart,
        ?CarbonImmutable $existingEnd,
    ): bool {
        if ($existingEnd !== null && $newStart->greaterThan($existingEnd)) {
            return false;
        }

        if ($newEnd !== null && $newEnd->lessThan($existingStart)) {
            return false;
        }

        return true;
    }
}
