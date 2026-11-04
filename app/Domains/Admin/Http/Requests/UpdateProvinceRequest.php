<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Province;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProvinceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('province'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueNameInCountryIgnoringSelf()],
            'status' => ['required', Rule::enum(GeographyStatus::class)],
        ];
    }

    private function uniqueNameInCountryIgnoringSelf(): Closure
    {
        $province = $this->route('province');

        return function (string $attribute, mixed $value, Closure $fail) use ($province): void {
            if (! is_string($value) || $value === '' || ! $province instanceof Province) {
                return;
            }

            $exists = Province::query()
                ->where('country_id', $province->country_id)
                ->whereKeyNot($province->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                ->exists();

            if ($exists) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
