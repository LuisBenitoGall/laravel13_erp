<?php

namespace App\Domains\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductImagePublicRequest extends FormRequest
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
