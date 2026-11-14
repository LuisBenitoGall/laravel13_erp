<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Sector;
use App\Support\I18n\LocaleResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Sector::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : SectorStatus::Active->value,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $locales = app(LocaleResolver::class);
        $default = $locales->default();

        $rules = [
            'name' => ['required', 'array'],
            'status' => ['required', Rule::enum(SectorStatus::class)],
        ];

        foreach ($locales->supported() as $locale) {
            $presence = $locale === $default ? 'required' : 'nullable';
            $rules['name.'.$locale] = [$presence, 'string', 'max:255'];
        }

        return $rules;
    }
}
