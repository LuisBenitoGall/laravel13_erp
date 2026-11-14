<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCompanyParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Company $company */
        $company = $this->route('company');

        return $this->user()?->can('update', $company) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('parent_id') === '' || $this->input('parent_id') === null) {
            $this->merge(['parent_id' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();
        /** @var Company $company */
        $company = $this->route('company');

        $linkedIds = Company::query()
            ->forUser($user)
            ->whereKeyNot($company->id)
            ->pluck('id')
            ->all();

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::in($linkedIds),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $parentId = $this->input('parent_id');
            if ($parentId === null || $parentId === '') {
                return;
            }

            /** @var Company $company */
            $company = $this->route('company');
            $parent = Company::query()->find((int) $parentId);

            if ($parent === null || $company->wouldCycleWithParent($parent)) {
                $validator->errors()->add('parent_id', __('jerarquia_ciclo_rechazado'));
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'parent_id.in' => __('jerarquia_fuera_vinculo'),
        ];
    }
}
