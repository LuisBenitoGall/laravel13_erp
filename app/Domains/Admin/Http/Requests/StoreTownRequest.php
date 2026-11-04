<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Town;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTownRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Town::class);
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
            'name' => ['required', 'string', 'max:255', $this->uniqueNameInProvince()],
            'status' => ['required', Rule::enum(GeographyStatus::class)],
        ];
    }

    private function uniqueNameInProvince(): Closure
    {
        $province = $this->route('province');
        $provinceId = $province instanceof Province ? $province->id : null;

        return function (string $attribute, mixed $value, Closure $fail) use ($provinceId): void {
            if (! is_string($value) || $value === '' || $provinceId === null) {
                return;
            }

            $exists = Town::query()
                ->where('province_id', $provinceId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                ->exists();

            if ($exists) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
