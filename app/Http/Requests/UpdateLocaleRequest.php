<?php

namespace App\Http\Requests;

use App\Support\I18n\LocaleResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $supported = app(LocaleResolver::class)->supported();

        return [
            'locale' => ['required', 'string', Rule::in($supported)],
        ];
    }
}
