<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\FunctionalityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFunctionalityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('functionality'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'module_id' => ['required', 'exists:modules,id'],
            'name' => ['required', 'string', 'max:255'],
            'label' => ['required', 'array'],
            'label.es' => ['required', 'string', 'max:255'],
            'label.en' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(FunctionalityStatus::class)],
            // slug intentionally absent — immutable (R-AUT-07)
        ];
    }
}
