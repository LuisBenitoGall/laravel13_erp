<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProvinceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Province::class);
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
            'name' => ['required', 'string', 'max:255', $this->uniqueNameInCountry()],
            'status' => ['required', Rule::enum(GeographyStatus::class)],
        ];
    }

    private function uniqueNameInCountry(): Closure
    {
        $country = $this->route('country');
        $countryId = $country instanceof Country ? $country->id : null;

        return function (string $attribute, mixed $value, Closure $fail) use ($countryId): void {
            if (! is_string($value) || $value === '' || $countryId === null) {
                return;
            }

            $exists = Province::query()
                ->where('country_id', $countryId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                ->exists();

            if ($exists) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
