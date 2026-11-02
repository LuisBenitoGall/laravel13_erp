<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Support\I18n\LocaleResolver;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreFunctionalityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Functionality::class);
    }

    protected function prepareForValidation(): void
    {
        $name = (string) $this->input('name', '');
        $status = $this->input('status');
        $module = $this->route('module');

        $merge = [
            'slug' => Str::slug($name),
            'status' => is_string($status) && $status !== ''
                ? $status
                : FunctionalityStatus::Active->value,
        ];

        if ($module instanceof Module) {
            $merge['module_id'] = $module->id;
        }

        $this->merge($merge);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $locales = app(LocaleResolver::class);
        $default = $locales->default();

        $rules = [
            'module_id' => ['required', 'exists:modules,id'],
            'name' => ['required', 'string', 'max:255', $this->uniqueCi('name')],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', $this->uniqueCi('slug')],
            'label' => ['required', 'array'],
            'status' => ['required', Rule::enum(FunctionalityStatus::class)],
        ];

        foreach ($locales->supported() as $locale) {
            $presence = $locale === $default ? 'required' : 'nullable';
            $rules['label.'.$locale] = [$presence, 'string', 'max:255'];
        }

        return $rules;
    }

    private function uniqueCi(string $column): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($column): void {
            if (! in_array($column, ['name', 'slug'], true) || ! is_string($value) || $value === '') {
                return;
            }

            $exists = Functionality::query()
                ->whereRaw('LOWER('.$column.') = ?', [mb_strtolower($value)])
                ->exists();

            if ($exists) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
