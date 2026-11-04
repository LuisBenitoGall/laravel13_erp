<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\VatTypeStatus;
use App\Domains\Admin\Models\VatType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVatTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', VatType::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');
        $re = $this->input('equivalence_surcharge');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : VatTypeStatus::Active->value,
            'equivalence_surcharge' => $re === '' || $re === null ? null : $re,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $countryId = $this->input('country_id');

        return [
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('vat_types', 'name')
                    ->where(fn ($q) => $q->where('country_id', $countryId)->whereNull('deleted_at')),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'equivalence_surcharge' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'status' => ['required', Rule::enum(VatTypeStatus::class)],
        ];
    }
}
