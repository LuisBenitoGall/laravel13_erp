<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\VatTypeStatus;
use App\Domains\Admin\Models\VatType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVatTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('vatType'));
    }

    protected function prepareForValidation(): void
    {
        $re = $this->input('equivalence_surcharge');

        $this->merge([
            'equivalence_surcharge' => $re === '' || $re === null ? null : $re,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var VatType $vatType */
        $vatType = $this->route('vatType');
        $countryId = $this->input('country_id', $vatType->country_id);

        return [
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('vat_types', 'name')
                    ->where(fn ($q) => $q->where('country_id', $countryId)->whereNull('deleted_at'))
                    ->ignore($vatType->id),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'equivalence_surcharge' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'status' => ['sometimes', Rule::enum(VatTypeStatus::class)],
        ];
    }
}
