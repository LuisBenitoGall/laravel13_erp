<?php

namespace App\Domains\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $teamKey = config('permission.column_names.team_foreign_key');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'not_in:Super Admin',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->whereNull($teamKey)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'universal' => ['required', 'boolean'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'max:255'],
        ];
    }
}
