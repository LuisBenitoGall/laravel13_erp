<?php

namespace App\Domains\Catalog\Http\Requests;

use App\Domains\Catalog\Enums\FamilyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('family'));
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
