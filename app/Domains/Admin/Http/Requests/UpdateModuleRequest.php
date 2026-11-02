<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Support\ErpIconCatalog;
use App\Support\I18n\LocaleResolver;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('module'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $locales = app(LocaleResolver::class);
        $default = $locales->default();

        $rules = [
            'name' => ['required', 'string', 'max:255', $this->uniqueNameIgnoringSelf()],
            'label' => ['required', 'array'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['required', 'string', Rule::in(ErpIconCatalog::names())],
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

    private function uniqueNameIgnoringSelf(): Closure
    {
        $module = $this->route('module');
        $ignoreId = $module instanceof Module ? $module->id : null;

        return function (string $attribute, mixed $value, Closure $fail) use ($ignoreId): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $query = Module::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($value)]);

            if ($ignoreId !== null) {
                $query->whereKeyNot($ignoreId);
            }

            if ($query->exists()) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
