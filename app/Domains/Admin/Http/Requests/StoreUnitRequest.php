<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\UnitStatus;
use App\Domains\Admin\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Unit::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : UnitStatus::Active->value,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('units', 'name')->whereNull('deleted_at'),
            ],
            'symbol' => ['nullable', 'string', 'max:4'],
            'status' => ['required', Rule::enum(UnitStatus::class)],
        ];
    }
}
