<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Country;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('country'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueNameIgnoringSelf()],
            'code' => ['nullable', 'string', 'max:32'],
            'alfa2' => ['nullable', 'string', 'size:2'],
            'alfa3' => ['nullable', 'string', 'size:3'],
            'flag' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(GeographyStatus::class)],
        ];
    }

    private function uniqueNameIgnoringSelf(): Closure
    {
        $country = $this->route('country');
        $ignoreId = $country instanceof Country ? $country->id : null;

        return function (string $attribute, mixed $value, Closure $fail) use ($ignoreId): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $query = Country::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($value)]);

            if ($ignoreId !== null) {
                $query->whereKeyNot($ignoreId);
            }

            if ($query->exists()) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
