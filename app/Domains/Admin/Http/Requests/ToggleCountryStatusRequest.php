<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\GeographyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleCountryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('country'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(GeographyStatus::class)],
        ];
    }
}
