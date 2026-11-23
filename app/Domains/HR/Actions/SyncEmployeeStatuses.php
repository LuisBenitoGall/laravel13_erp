<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\Employee;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Sync multi-tenant de estados (sin CurrentCompany): caduca actives vencidos
 * y activa scheduled elegibles respetando ≤1 active por (user, company).
 *
 * @phpstan-type SyncResult array{expired: int, activated: int}
 */
class SyncEmployeeStatuses
{
    /**
     * @return SyncResult
     */
    public function handle(?CarbonInterface $today = null): array
    {
        $todayDate = ($today ?? now())->copy()->startOfDay()->toDateString();

        return DB::transaction(function () use ($todayDate): array {
            $expired = Employee::query()
                ->withoutGlobalScope('company')
                ->where('status', EmployeeStatus::Active)
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<', $todayDate)
                ->update(['status' => EmployeeStatus::Ended->value]);

            $activated = 0;

            $candidates = Employee::query()
                ->withoutGlobalScope('company')
                ->where('status', EmployeeStatus::Scheduled)
                ->whereDate('start_date', '<=', $todayDate)
                ->orderBy('start_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($candidates as $employee) {
                $hasActive = Employee::query()
                    ->withoutGlobalScope('company')
                    ->where('user_id', $employee->user_id)
                    ->where('company_id', $employee->company_id)
                    ->where('status', EmployeeStatus::Active)
                    ->exists();

                if ($hasActive) {
                    continue;
                }

                $employee->update(['status' => EmployeeStatus::Active->value]);
                $activated++;
            }

            return [
                'expired' => $expired,
                'activated' => $activated,
            ];
        });
    }
}
