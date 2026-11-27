<?php

namespace App\Domains\Calendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDayTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('day_type'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_working' => $this->boolean('is_working'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:32', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_working' => ['boolean'],
        ];
    }
}
