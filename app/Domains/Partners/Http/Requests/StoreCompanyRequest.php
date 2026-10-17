<?php

namespace App\Domains\Partners\Http\Requests;

use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
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
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', 'unique:companies,slug'],
            'tradename' => ['nullable', 'string', 'max:255'],
            'nif' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('companies', 'nif')->whereNull('deleted_at'),
            ],
            'status' => ['required', Rule::enum(CompanyStatus::class)],
            'logo' => ['nullable', 'string', 'max:255'],
        ];
    }
}
