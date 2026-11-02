<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Support\ErpIconCatalog;
use App\Support\I18n\LocaleResolver;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Module::class);
    }

    protected function prepareForValidation(): void
    {
        $name = (string) $this->input('name', '');
        $status = $this->input('status');

        $this->merge([
            'slug' => Str::slug($name),
            'status' => is_string($status) && $status !== ''
                ? $status
                : ModuleStatus::Active->value,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $locales = app(LocaleResolver::class);
        $default = $locales->default();

        $rules = [
            'name' => ['required', 'string', 'max:255', $this->uniqueCi('name')],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', $this->uniqueCi('slug')],
            'label' => ['required', 'array'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['required', 'string', Rule::in(ErpIconCatalog::names())],
            'level' => ['required', Rule::enum(ModuleLevel::class)],
            'status' => ['required', Rule::enum(ModuleStatus::class)],
            'explanation' => ['required', 'array'],
        ];

        foreach ($locales->supported() as $locale) {
            $presence = $locale === $default ? 'required' : 'nullable';
            $rules['label.'.$locale] = [$presence, 'string', 'max:255'];
            $rules['explanation.'.$locale] = [$presence, 'string'];
        }

        return $rules;
    }

    private function uniqueCi(string $column): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($column): void {
            if (! in_array($column, ['name', 'slug'], true) || ! is_string($value) || $value === '') {
                return;
            }

            $exists = Module::query()
                ->whereRaw('LOWER('.$column.') = ?', [mb_strtolower($value)])
                ->exists();

            if ($exists) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
