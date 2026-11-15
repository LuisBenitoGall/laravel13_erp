<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Rules\ValidCompanyTaxId;
use App\Support\I18n\LocaleResolver;
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
        $locales = app(LocaleResolver::class);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'tradename' => ['nullable', 'string', 'max:255'],
            'nif' => [
                'required',
                'string',
                'max:12',
                new ValidCompanyTaxId,
                Rule::unique('companies', 'nif')->whereNull('deleted_at')->ignore($company->id),
            ],
            'status' => ['required', Rule::enum(CompanyStatus::class)],
            'excerpt' => ['sometimes', 'nullable', 'array'],
            'description' => ['sometimes', 'nullable', 'array'],
        ];

        foreach ($locales->supported() as $locale) {
            $rules['excerpt.'.$locale] = ['nullable', 'string', 'max:1000'];
            $rules['description.'.$locale] = ['nullable', 'string', 'max:20000'];
        }

        return $rules;
    }
}
