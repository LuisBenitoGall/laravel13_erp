<?php

namespace App\Domains\HR\Http\Requests;

use App\Domains\HR\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class EndEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return $this->user()->can('end', $employee);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'end_date' => $this->filled('end_date') ? $this->input('end_date') : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return [
            'end_date' => ['nullable', 'date', 'after_or_equal:'.$employee->start_date->toDateString()],
        ];
    }
}
