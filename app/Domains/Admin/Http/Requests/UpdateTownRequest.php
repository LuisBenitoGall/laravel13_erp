<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Town;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTownRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('town'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueNameInProvinceIgnoringSelf()],
            'status' => ['required', Rule::enum(GeographyStatus::class)],
        ];
    }

    private function uniqueNameInProvinceIgnoringSelf(): Closure
    {
        $town = $this->route('town');

        return function (string $attribute, mixed $value, Closure $fail) use ($town): void {
            if (! is_string($value) || $value === '' || ! $town instanceof Town) {
                return;
            }

            $exists = Town::query()
                ->where('province_id', $town->province_id)
                ->whereKeyNot($town->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                ->exists();

            if ($exists) {
                $fail(__('validation.unique', ['attribute' => $attribute]));
            }
        };
    }
}
