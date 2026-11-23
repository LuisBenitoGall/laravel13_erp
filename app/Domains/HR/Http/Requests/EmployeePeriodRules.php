<?php

namespace App\Domains\HR\Http\Requests;

use App\Domains\HR\Models\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Reglas comunes de los campos de un periodo laboral (create / update / renew).
 * FKs siempre acotadas al tenant (R-TEN-07).
 *
 * @mixin FormRequest
 */
trait EmployeePeriodRules
{
    protected function normalizePeriodInput(): void
    {
        $this->merge([
            'time_control' => $this->boolean('time_control'),
            'workplace_id' => $this->filled('workplace_id') ? $this->input('workplace_id') : null,
            'job_title' => $this->filled('job_title') ? $this->input('job_title') : null,
            'end_date' => $this->filled('end_date') ? $this->input('end_date') : null,
            'year_salary' => $this->filled('year_salary') ? $this->input('year_salary') : null,
            'observations' => $this->filled('observations') ? $this->input('observations') : null,
        ]);
    }

    /**
     * Tipo temporal (duration no null) ⇒ end_date obligatorio.
     * Tipo indefinido (duration null) ⇒ end_date opcional.
     */
    protected function contractTypeRequiresEndDate(): bool
    {
        $contractTypeId = $this->integer('contract_type_id');

        if ($contractTypeId <= 0) {
            return false;
        }

        $type = ContractType::query()->withTrashed()->find($contractTypeId);

        return $type !== null && ! $type->isIndefinite();
    }

    /**
     * @param  int|null  $keepContractTypeId  tipo ya asignado (se admite aunque esté inactivo/borrado)
     * @param  int|null  $keepWorkplaceId  centro ya asignado (idem)
     * @return array<string, mixed>
     */
    protected function periodRules(
        ?int $companyId,
        ?int $keepContractTypeId = null,
        ?int $keepWorkplaceId = null,
    ): array {
        return [
            'contract_type_id' => [
                'required',
                'integer',
                Rule::exists('contract_types', 'id')
                    ->where('company_id', $companyId)
                    ->where(function ($query) use ($keepContractTypeId): void {
                        $query->where(function ($inner): void {
                            $inner->whereNull('deleted_at')->where('status', 'active');
                        })->when(
                            $keepContractTypeId !== null,
                            fn ($q) => $q->orWhere('id', $keepContractTypeId),
                        );
                    }),
            ],
            'workplace_id' => [
                'nullable',
                'integer',
                Rule::exists('workplaces', 'id')
                    ->where('company_id', $companyId)
                    ->where(function ($query) use ($keepWorkplaceId): void {
                        $query->where(function ($inner): void {
                            $inner->whereNull('deleted_at')->where('status', 'active');
                        })->when(
                            $keepWorkplaceId !== null,
                            fn ($q) => $q->orWhere('id', $keepWorkplaceId),
                        );
                    }),
            ],
            'job_title' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => [
                Rule::requiredIf(fn (): bool => $this->contractTypeRequiresEndDate()),
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'weekly_hours' => ['required', 'numeric', 'min:0', 'max:168', 'decimal:0,2'],
            'time_control' => ['boolean'],
            'year_salary' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'observations' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
