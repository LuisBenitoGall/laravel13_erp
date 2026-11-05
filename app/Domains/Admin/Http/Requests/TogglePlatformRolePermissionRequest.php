<?php

namespace App\Domains\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TogglePlatformRolePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'permission' => ['required', 'string', 'max:255'],
            'enabled' => ['required', 'boolean'],
        ];
    }
}
