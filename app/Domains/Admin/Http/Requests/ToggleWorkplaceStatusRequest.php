<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\WorkplaceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleWorkplaceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workplace'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(WorkplaceStatus::class)],
        ];
    }
}
