<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Http\Requests\Concerns\ValidatesCompanyCurrenciesSync;
use App\Domains\Admin\Models\Company;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SyncCompanySettingsCurrenciesRequest extends FormRequest
{
    use ValidatesCompanyCurrenciesSync;

    private ?Company $sessionCompany = null;

    public function authorize(): bool
    {
        return $this->user()?->can('updateCompanySettings') ?? false;
    }

    public function company(): Company
    {
        if ($this->sessionCompany !== null) {
            return $this->sessionCompany;
        }

        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 403);

        $company = Company::query()->find($companyId);
        abort_if($company === null, 403);

        return $this->sessionCompany = $company;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->companyCurrenciesRules();
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateCompanyCurrenciesSync($validator, $this->company());
    }
}
