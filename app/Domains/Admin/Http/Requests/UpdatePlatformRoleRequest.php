<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Role $role */
        $role = $this->route('role');
        $teamKey = config('permission.column_names.team_foreign_key');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->whereNull($teamKey))
                    ->ignore($role->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'universal' => ['sometimes', 'boolean'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'max:255'],
        ];
    }
}
