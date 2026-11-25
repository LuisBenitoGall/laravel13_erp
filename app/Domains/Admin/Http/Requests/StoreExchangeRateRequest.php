<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\ExchangeRate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ExchangeRate::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'from_currency_id' => [
                'required',
                'integer',
                Rule::exists('currencies', 'id')->where('status', CurrencyStatus::Active->value)->whereNull('deleted_at'),
            ],
            'to_currency_id' => [
                'required',
                'integer',
                'different:from_currency_id',
                Rule::exists('currencies', 'id')->where('status', CurrencyStatus::Active->value)->whereNull('deleted_at'),
            ],
            'rate_date' => ['required', 'date'],
            'rate_ab' => ['nullable', 'numeric', 'gt:0', 'decimal:0,6'],
            'rate_ba' => ['nullable', 'numeric', 'gt:0', 'decimal:0,6'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $ab = $this->input('rate_ab');
            $ba = $this->input('rate_ba');

            if (($ab === null || $ab === '') && ($ba === null || $ba === '')) {
                $v->errors()->add('rate_ab', __('conversion_requiere_rate'));
            }
        });
    }
}
