<?php

namespace App\Domains\Partners\Http\Requests;

use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Company $company */
        $company = $this->route('company');

        return $this->user()?->can('update', $company) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(CompanyStatus::class)],
        ];
    }
}
