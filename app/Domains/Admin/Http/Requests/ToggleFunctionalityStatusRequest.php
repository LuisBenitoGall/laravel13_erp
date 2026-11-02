<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\FunctionalityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleFunctionalityStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('functionality'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(FunctionalityStatus::class)],
        ];
    }
}
