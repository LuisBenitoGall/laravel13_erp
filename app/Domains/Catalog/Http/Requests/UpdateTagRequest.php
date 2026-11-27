<?php

namespace App\Domains\Catalog\Http\Requests;

use App\Domains\Catalog\Enums\TagStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('tag'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(TagStatus::class)],
            'slug' => ['prohibited'],
        ];
    }
}
