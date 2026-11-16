<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Preference;
use Illuminate\Foundation\Http\FormRequest;

class StorePreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Preference::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:760'],
        ];
    }
}
