<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Town;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Support\DelegationPerimeter;
use App\Rules\ValidCompanyTaxId;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

        $ids = $this->input('workplace_ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids, fn ($id): bool => $id !== null && $id !== '')) : [];

        $this->merge([
            'workplace_ids' => array_map('intval', $ids),
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
        $matrixId = app(CurrentCompany::class)->id();

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
            'workplace' => ['nullable', 'array'],
            'workplace.name' => ['nullable', 'string', 'max:255'],
            'workplace.address' => ['nullable', 'string', 'max:255'],
            'workplace.cp' => ['nullable', 'string', 'max:16'],
            'workplace.town_id' => [
                'nullable',
                'integer',
                Rule::exists(Town::class, 'id')->whereNull('deleted_at'),
            ],
            'workplace_ids' => ['nullable', 'array'],
            'workplace_ids.*' => [
                'integer',
                Rule::exists(Workplace::class, 'id')
                    ->where('company_id', $matrixId)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $name = $this->input('workplace.name');
            $ids = $this->input('workplace_ids', []);
            $ids = is_array($ids) ? $ids : [];

            if (! filled($name) && $ids === []) {
                $validator->errors()->add('workplace.name', __('delegacion_requiere_centro'));
            }
        });
    }
}
