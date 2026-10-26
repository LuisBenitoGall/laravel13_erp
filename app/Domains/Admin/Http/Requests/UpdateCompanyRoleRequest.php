<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Role;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Role $role */
        $role = $this->route('companyRole');
        $teamKey = config('permission.column_names.team_foreign_key');
        $companyId = app(CurrentCompany::class)->id();

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->where($teamKey, $companyId))
                    ->ignore($role->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'max:255'],
        ];
    }
}
