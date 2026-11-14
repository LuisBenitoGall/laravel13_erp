<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\SectorStatus;
use App\Support\I18n\LocaleResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('sector')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $locales = app(LocaleResolver::class);
        $default = $locales->default();

        $rules = [
            'name' => ['required', 'array'],
            'status' => ['sometimes', Rule::enum(SectorStatus::class)],
        ];

        foreach ($locales->supported() as $locale) {
            $presence = $locale === $default ? 'required' : 'nullable';
            $rules['name.'.$locale] = [$presence, 'string', 'max:255'];
        }

        return $rules;
    }
}
