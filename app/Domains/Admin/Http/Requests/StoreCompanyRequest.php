<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Rules\ValidCompanyTaxId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Company::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tradename' => ['nullable', 'string', 'max:255'],
            'nif' => [
                'required',
                'string',
                'max:12',
                new ValidCompanyTaxId,
                Rule::unique('companies', 'nif')->whereNull('deleted_at'),
            ],
            'status' => ['required', Rule::enum(CompanyStatus::class)],
        ];
    }
}
