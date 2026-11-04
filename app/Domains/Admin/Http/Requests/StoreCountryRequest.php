<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Country;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Country::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : GeographyStatus::Active->value,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueName()],
            'code' => ['nullable', 'string', 'max:32'],
            'alfa2' => ['nullable', 'string', 'size:2'],
            'alfa3' => ['nullable', 'string', 'size:3'],
            'flag' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(GeographyStatus::class)],
        ];
    }

    private function uniqueName(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $exists = Country::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                ->exists();

            if ($exists) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
