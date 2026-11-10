<?php

namespace App\Domains\Admin\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $target = $this->route('user');
        $targetId = $target instanceof User
            ? $target->id
            : $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($targetId),
            ],
            'nif' => ['nullable', 'string', 'max:32'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge(['status' => $this->boolean('status')]);
        }
        if ($this->has('is_public')) {
            $this->merge(['is_public' => $this->boolean('is_public')]);
        }
    }
}
