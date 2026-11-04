<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CurrencyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleCurrencyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('currency'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(CurrencyStatus::class)],
        ];
    }
}
