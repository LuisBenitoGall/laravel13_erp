<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\ModuleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleModuleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('module'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ModuleStatus::class)],
        ];
    }
}
