<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Account::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : AccountStatus::Active->value,
            'is_default' => $this->boolean('is_default'),
            'duration' => $this->filled('duration') ? $this->input('duration') : null,
            'description' => $this->filled('description') ? $this->input('description') : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('accounts', 'name')->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'rate' => ['required', 'numeric', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(AccountStatus::class)],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
