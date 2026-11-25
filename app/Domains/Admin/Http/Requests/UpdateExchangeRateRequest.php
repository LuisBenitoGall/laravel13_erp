<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\ExchangeRate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $exchangeRate = $this->route('exchange_rate');

        return $exchangeRate instanceof ExchangeRate && $this->user()->can('update', $exchangeRate);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
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
