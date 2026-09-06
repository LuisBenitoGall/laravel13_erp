<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Module::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', 'unique:modules,slug'],
            'label' => ['required', 'array'],
            'label.es' => ['required', 'string', 'max:255'],
            'label.en' => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:32'],
            'icon' => ['required', 'string', 'max:64'],
            'level' => ['required', Rule::enum(ModuleLevel::class)],
            'status' => ['required', Rule::enum(ModuleStatus::class)],
            'explanation' => ['nullable', 'array'],
            'explanation.es' => ['nullable', 'string'],
            'explanation.en' => ['nullable', 'string'],
        ];
    }
}
