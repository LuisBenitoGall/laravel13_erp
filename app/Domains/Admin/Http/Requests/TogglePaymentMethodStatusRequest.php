<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\PaymentMethodStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TogglePaymentMethodStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('payment_method'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(PaymentMethodStatus::class)],
        ];
    }
}
