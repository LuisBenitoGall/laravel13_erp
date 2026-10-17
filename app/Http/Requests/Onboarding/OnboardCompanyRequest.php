<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nif' => [
                'required',
                'string',
                'max:32',
                Rule::unique('companies', 'nif')->whereNull('deleted_at'),
            ],
            'name' => ['required_without:tradename', 'nullable', 'string', 'max:255'],
            'tradename' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'logo' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,svg',
                'max:2048',
            ],
        ];
    }
}
