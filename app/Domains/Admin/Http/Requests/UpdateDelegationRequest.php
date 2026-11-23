<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Support\DelegationPerimeter;
use App\Rules\ValidCompanyTaxId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDelegationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Company $delegation */
        $delegation = $this->route('delegation');
        app(DelegationPerimeter::class)->assertOwns($delegation);

        return $this->user()->can('updateDelegation', $delegation);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Company $delegation */
        $delegation = $this->route('delegation');

        return [
            'name' => ['required', 'string', 'max:255'],
            'tradename' => ['nullable', 'string', 'max:255'],
            'nif' => [
                'required',
                'string',
                'max:12',
                new ValidCompanyTaxId,
                Rule::unique('companies', 'nif')->whereNull('deleted_at')->ignore($delegation->id),
            ],
            'status' => ['required', Rule::enum(CompanyStatus::class)],
        ];
    }
}
