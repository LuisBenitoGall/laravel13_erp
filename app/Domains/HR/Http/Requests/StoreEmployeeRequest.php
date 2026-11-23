<?php

namespace App\Domains\HR\Http\Requests;

use App\Domains\HR\Actions\AssertEmployeeNoOverlap;
use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\Employee;
use App\Support\Tenancy\CurrentCompany;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEmployeeRequest extends FormRequest
{
    use EmployeePeriodRules;

    public function authorize(): bool
    {
        return $this->user()->can('create', Employee::class);
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePeriodInput();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();

        return [
            // R-TEN-07 + membresía: User existente Y en user_companies de la company activa.
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
                Rule::exists('user_companies', 'user_id')->where('company_id', $companyId),
            ],
            ...$this->periodRules($companyId),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $companyId = app(CurrentCompany::class)->id();
            if ($companyId === null) {
                return;
            }

            $userId = (int) $this->input('user_id');
            $start = CarbonImmutable::parse((string) $this->input('start_date'))->startOfDay();
            $end = filled($this->input('end_date'))
                ? CarbonImmutable::parse((string) $this->input('end_date'))->startOfDay()
                : null;

            $status = EmployeeStatus::initialFor($start, $end);
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

            app(AssertEmployeeNoOverlap::class)->handle(
                $userId,
                $companyId,
                $start,
                $end,
                $exceptIds,
            );
        });
    }
}
