<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Currency::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : CurrencyStatus::Active->value,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable', 'string', 'max:3',
                Rule::unique('currencies', 'code')->whereNull('deleted_at'),
            ],
            'number' => [
                'nullable', 'string', 'max:4',
                Rule::unique('currencies', 'number')->whereNull('deleted_at'),
            ],
            'symbol' => ['nullable', 'string', 'max:8'],
            'status' => ['required', Rule::enum(CurrencyStatus::class)],
        ];
    }
}
