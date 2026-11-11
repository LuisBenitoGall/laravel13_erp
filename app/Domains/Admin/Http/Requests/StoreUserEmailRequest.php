<?php

namespace App\Domains\Admin\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $owner = $this->owner();
        $principal = $owner?->email;

        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('user_emails', 'email'),
                Rule::notIn($principal !== null && $principal !== '' ? [$principal] : []),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.unique' => __('email_adicional_duplicado'),
            'email.not_in' => __('email_adicional_igual_principal'),
        ];
    }

    private function owner(): ?User
    {
        $routeUser = $this->route('user');
        if ($routeUser instanceof User) {
            return $routeUser;
        }

        $actor = $this->user();

        return $actor instanceof User ? $actor : null;
    }
}
