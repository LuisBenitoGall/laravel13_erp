<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AttachCompanyChildRequest extends FormRequest
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
            'child_id' => [
                'required',
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

            /** @var Company $company */
            $company = $this->route('company');
            $child = Company::query()->find((int) $this->input('child_id'));

            if ($child === null || $company->wouldCycleWithChild($child)) {
                $validator->errors()->add('child_id', __('jerarquia_ciclo_rechazado'));
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'child_id.required' => __('jerarquia_hijo_requerido'),
            'child_id.in' => __('jerarquia_fuera_vinculo'),
        ];
    }
}
