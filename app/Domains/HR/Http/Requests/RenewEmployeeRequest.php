<?php

namespace App\Domains\HR\Http\Requests;

use App\Domains\HR\Actions\AssertEmployeeNoOverlap;
use App\Domains\HR\Models\Employee;
use App\Support\Tenancy\CurrentCompany;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RenewEmployeeRequest extends FormRequest
{
    use EmployeePeriodRules;

    public function authorize(): bool
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return $this->user()->can('renew', $employee);
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePeriodInput();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->periodRules(app(CurrentCompany::class)->id());
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Employee $employee */
            $employee = $this->route('employee');

            $start = CarbonImmutable::parse((string) $this->input('start_date'))->startOfDay();
            $end = filled($this->input('end_date'))
                ? CarbonImmutable::parse((string) $this->input('end_date'))->startOfDay()
                : null;

            app(AssertEmployeeNoOverlap::class)->handle(
                (int) $employee->user_id,
                (int) $employee->company_id,
                $start,
                $end,
                [(int) $employee->id],
            );
        });
    }
}
