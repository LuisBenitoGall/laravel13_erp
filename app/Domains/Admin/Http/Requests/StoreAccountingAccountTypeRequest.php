<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\AccountingAccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountingAccountTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AccountingAccountType::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('accounting_account_types', 'code')->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
