<?php

namespace App\Domains\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'position' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array{position: ?string, department: ?string}
     */
    public function validated($key = null, $default = null): mixed
    {
        /** @var array{position?: ?string, department?: ?string} $data */
        $data = parent::validated($key, $default);

        if ($key !== null) {
            return $data;
        }

        return [
            'position' => $this->nullableTrimmed($data['position'] ?? null),
            'department' => $this->nullableTrimmed($data['department'] ?? null),
        ];
    }

    private function nullableTrimmed(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
