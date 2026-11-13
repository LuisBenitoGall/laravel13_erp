<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadCompanyLogoRequest extends FormRequest
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
        return [
            'logo' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'gif', 'webp'])
                    ->max(512),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'logo.required' => __('logo_requerido'),
            'logo.max' => __('logo_peso_maximo'),
            'logo.mimes' => __('imagen_formato_invalido'),
        ];
    }
}
