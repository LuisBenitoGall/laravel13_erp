<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Content::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('code') || ! is_string($this->input('code'))) {
            return;
        }

        $this->merge([
            'code' => Str::lower(trim($this->input('code'))),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'kind' => ['required', Rule::enum(ContentKind::class)],
            'code' => [
                Rule::requiredIf(fn (): bool => $this->input('kind') === ContentKind::Help->value),
                'nullable',
                'string',
                'max:64',
                // Segmentos a-z0-9_ unidos por . o - (p. ej. users.is_public, user_profile.x).
                'regex:/^[a-z0-9_]+(?:[.\-][a-z0-9_]+)*$/',
                Rule::unique('contents', 'code'),
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'kind' => __('validation.attributes.kind'),
            'code' => __('validation.attributes.code'),
        ];
    }
}
