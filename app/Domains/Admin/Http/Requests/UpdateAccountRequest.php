<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('account'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'duration' => $this->filled('duration') ? $this->input('duration') : null,
            'description' => $this->filled('description') ? $this->input('description') : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Account $account */
        $account = $this->route('account');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('accounts', 'name')->whereNull('deleted_at')->ignore($account->id),
            ],
            'description' => ['nullable', 'string'],
            'rate' => ['required', 'numeric', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::enum(AccountStatus::class)],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
