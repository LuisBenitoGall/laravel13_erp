<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\VatTypeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleVatTypeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('vatType'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(VatTypeStatus::class)],
        ];
    }
}
