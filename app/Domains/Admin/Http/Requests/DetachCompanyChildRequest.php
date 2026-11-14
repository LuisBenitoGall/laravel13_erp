<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DetachCompanyChildRequest extends FormRequest
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
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Company $company */
            $company = $this->route('company');
            /** @var Company $child */
            $child = $this->route('child');

            if ((int) $child->parent_id !== (int) $company->id) {
                $validator->errors()->add('child', __('jerarquia_hijo_no_pertenece'));
            }
        });
    }
}
