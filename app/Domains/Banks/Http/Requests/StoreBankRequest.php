<?php

namespace App\Domains\Banks\Http\Requests;

use App\Domains\Banks\Enums\BankStatus;
use App\Domains\Banks\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Bank::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : BankStatus::Active->value,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tradename' => ['nullable', 'string', 'max:255'],
            'lei' => ['nullable', 'string', 'max:255'],
            'eu_code' => [
                'required', 'string', 'max:255',
                Rule::unique('banks', 'eu_code')->whereNull('deleted_at'),
            ],
            'supervisor_code' => ['nullable', 'string', 'max:255'],
            'swift' => ['nullable', 'string', 'max:11'],
            'status' => ['required', Rule::enum(BankStatus::class)],
        ];
    }
}
