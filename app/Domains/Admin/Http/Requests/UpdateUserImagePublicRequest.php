<?php

namespace App\Domains\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserImagePublicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'public' => ['required', 'boolean'],
        ];
    }
}
