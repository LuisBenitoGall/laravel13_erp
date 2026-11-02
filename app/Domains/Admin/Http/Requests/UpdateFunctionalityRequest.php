<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\I18n\LocaleResolver;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFunctionalityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('functionality'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $locales = app(LocaleResolver::class);
        $default = $locales->default();

        $rules = [
            'label' => ['required', 'array'],
            // name / slug / module_id / status intentionally absent — modal Edit solo label
        ];

        foreach ($locales->supported() as $locale) {
            $presence = $locale === $default ? 'required' : 'nullable';
            $rules['label.'.$locale] = [$presence, 'string', 'max:255'];
        }

        return $rules;
    }
}
