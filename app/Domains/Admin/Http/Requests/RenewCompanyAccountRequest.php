<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenewCompanyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('profile.update') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')
                    ->where('status', AccountStatus::Active->value)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function account(): Account
    {
        return Account::query()->whereKey($this->validated('account_id'))->firstOrFail();
    }
}
