<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $teamKey = config('permission.column_names.team_foreign_key');
        $companyId = app(CurrentCompany::class)->id();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'not_in:Super Admin,manager,guest',
                Rule::unique('roles', 'name')->where(
                    fn ($q) => $q->where($teamKey, $companyId),
                ),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'max:255'],
        ];
    }
}
