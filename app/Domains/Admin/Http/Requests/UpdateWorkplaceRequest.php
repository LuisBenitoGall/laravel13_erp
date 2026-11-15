<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Town;
use App\Domains\Admin\Models\Workplace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkplaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Workplace $workplace */
        $workplace = $this->route('workplace');

        return $this->user()->can('update', $workplace);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'featured' => $this->boolean('featured'),
            'address' => $this->filled('address') ? $this->input('address') : null,
            'cp' => $this->filled('cp') ? $this->input('cp') : null,
            'town_id' => $this->filled('town_id') ? $this->input('town_id') : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'cp' => ['nullable', 'string', 'max:16'],
            'town_id' => [
                'nullable',
                'integer',
                Rule::exists(Town::class, 'id')->whereNull('deleted_at'),
            ],
            'featured' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::enum(WorkplaceStatus::class)],
        ];
    }
}
