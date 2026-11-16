<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Content::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'kind' => ['required', Rule::enum(ContentKind::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'kind' => __('validation.attributes.kind'),
        ];
    }
}
