<?php

namespace App\Domains\HR\Http\Requests;

use App\Domains\HR\Models\Employee;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    use EmployeePeriodRules;

    public function authorize(): bool
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return $this->user()->can('update', $employee);
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePeriodInput();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return $this->periodRules(
            app(CurrentCompany::class)->id(),
            $employee->contract_type_id,
            $employee->workplace_id,
        );
    }
}
