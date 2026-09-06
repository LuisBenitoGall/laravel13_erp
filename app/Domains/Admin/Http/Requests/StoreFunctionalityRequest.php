<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Models\Functionality;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFunctionalityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Functionality::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'module_id' => ['required', 'exists:modules,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', 'unique:functionalities,slug'],
            'label' => ['required', 'array'],
            'label.es' => ['required', 'string', 'max:255'],
            'label.en' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(FunctionalityStatus::class)],
        ];
    }
}
