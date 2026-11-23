<?php

namespace App\Domains\HR\Actions;

use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\Employee;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Alta de un periodo laboral. Si el periodo nuevo queda `active`, cierra
 * el `active` previo del mismo User en la company (≤1 active; indefinido → D−1).
 */
class CreateEmployee
{
    public function __construct(private readonly AssertEmployeeNoOverlap $assertNoOverlap) {}

    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Employee
    {
        return DB::transaction(function () use ($data): Employee {
            $userId = (int) $data['user_id'];
            $companyId = (int) ($data['company_id'] ?? app(CurrentCompany::class)->id());

            // Serializa altas/renovaciones concurrentes del mismo User.
            User::query()->whereKey($userId)->lockForUpdate()->first();

            $start = CarbonImmutable::parse((string) $data['start_date'])->startOfDay();
            $end = ! empty($data['end_date'])
                ? CarbonImmutable::parse((string) $data['end_date'])->startOfDay()
                : null;

            $status = EmployeeStatus::initialFor($start, $end);
            $data['status'] = $status->value;
            $data['company_id'] = $companyId;

            // Si el nuevo nace active, los actives actuales se cierran después → no bloquean.
            $exceptIds = $status === EmployeeStatus::Active
                ? Employee::query()
                    ->withoutGlobalScope('company')
                    ->where('user_id', $userId)
                    ->where('company_id', $companyId)
                    ->where('status', EmployeeStatus::Active)
                    ->pluck('id')
                    ->map(fn ($id): int => (int) $id)
                    ->all()
                : [];

            $this->assertNoOverlap->handle($userId, $companyId, $start, $end, $exceptIds);

            if ($status === EmployeeStatus::Active) {
                $this->closeActivePeriods($userId, $companyId, $start);
            }

            return Employee::query()->create($data);
        });
    }

    private function closeActivePeriods(int $userId, int $companyId, CarbonImmutable $newStart): void
    {
        Employee::query()
            ->where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('status', EmployeeStatus::Active)
            ->lockForUpdate()
            ->get()
            ->each(function (Employee $active) use ($newStart): void {
                $active->update([
                    'status' => EmployeeStatus::Ended,
                    // Indefinido: forzar D−1 (nunca conservar null).
                    'end_date' => $active->end_date ?? $this->closingDate($active, $newStart),
                ]);
            });
    }

    /** Día previo al nuevo periodo, sin quedar antes del inicio del cerrado. */
    private function closingDate(Employee $closing, CarbonImmutable $newStart): CarbonImmutable
    {
        $candidate = $newStart->subDay();
        $closingStart = CarbonImmutable::parse($closing->start_date->toDateString())->startOfDay();

        return $candidate->lessThan($closingStart) ? $closingStart : $candidate;
    }
}
