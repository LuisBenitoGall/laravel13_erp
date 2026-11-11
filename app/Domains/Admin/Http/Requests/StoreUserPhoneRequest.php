<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\PhoneType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserPhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $owner = $this->owner();

        return [
            'e164' => [
                'required',
                'string',
                'max:30',
                Rule::unique('phones', 'e164')
                    ->where(fn ($query) => $query
                        ->where('phoneable_type', $owner?->getMorphClass())
                        ->where('phoneable_id', $owner?->id)
                        ->whereNull('deleted_at')),
            ],
            'ext' => ['nullable', 'string', 'max:10'],
            'type' => ['required', Rule::enum(PhoneType::class)],
            'label' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'is_whatsapp' => ['sometimes', 'boolean'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'e164.unique' => __('telefono_e164_duplicado'),
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
