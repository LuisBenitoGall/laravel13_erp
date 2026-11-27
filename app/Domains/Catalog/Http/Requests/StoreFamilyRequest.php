<?php

namespace App\Domains\Catalog\Http\Requests;

use App\Domains\Catalog\Enums\FamilyStatus;
use App\Domains\Catalog\Models\Family;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Family::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : FamilyStatus::Inactive->value,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(FamilyStatus::class)],
            'slug' => ['prohibited'],
        ];
    }
}
