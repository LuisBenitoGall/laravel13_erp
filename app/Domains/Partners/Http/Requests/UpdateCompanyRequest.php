<?php

namespace App\Domains\Partners\Http\Requests;

use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Company $company */
        $company = $this->route('company');

        return $this->user()->can('update', $company);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Company $company */
        $company = $this->route('company');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:64',
                'alpha_dash',
                Rule::unique('companies', 'slug')->ignore($company->id),
            ],
            'tradename' => ['nullable', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:32'],
            'status' => ['required', Rule::enum(CompanyStatus::class)],
            'logo' => ['nullable', 'string', 'max:255'],
        ];
    }
}
