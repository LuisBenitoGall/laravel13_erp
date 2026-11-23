<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Town;
use App\Domains\Admin\Support\DelegationPerimeter;
use App\Rules\ValidCompanyTaxId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDelegationRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(DelegationPerimeter::class)->matrix();

        return $this->user()->can('createDelegation');
    }

    protected function prepareForValidation(): void
    {
        $workplace = $this->input('workplace');
        $workplace = is_array($workplace) ? $workplace : [];

        $this->merge([
            'workplace' => [
                'name' => $workplace['name'] ?? null,
                'address' => ($workplace['address'] ?? '') !== '' ? $workplace['address'] : null,
                'cp' => ($workplace['cp'] ?? '') !== '' ? $workplace['cp'] : null,
                'town_id' => ($workplace['town_id'] ?? '') !== '' ? $workplace['town_id'] : null,
            ],
        ]);
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
            'workplace' => ['required', 'array'],
            'workplace.name' => ['required', 'string', 'max:255'],
            'workplace.address' => ['nullable', 'string', 'max:255'],
            'workplace.cp' => ['nullable', 'string', 'max:16'],
            'workplace.town_id' => [
                'nullable',
                'integer',
                Rule::exists(Town::class, 'id')->whereNull('deleted_at'),
            ],
        ];
    }
}
