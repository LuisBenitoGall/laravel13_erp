<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CurrencyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** D4: sin reglas para `code` / `number` (inmutables; `validated()` los descarta). */
class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('currency'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:8'],
            'status' => ['sometimes', Rule::enum(CurrencyStatus::class)],
        ];
    }
}
