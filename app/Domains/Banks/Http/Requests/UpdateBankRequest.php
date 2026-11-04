<?php

namespace App\Domains\Banks\Http\Requests;

use App\Domains\Banks\Enums\BankStatus;
use App\Domains\Banks\Models\Bank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('bank'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Bank $bank */
        $bank = $this->route('bank');

        return [
            'name' => ['required', 'string', 'max:255'],
            'tradename' => ['nullable', 'string', 'max:255'],
            'lei' => ['nullable', 'string', 'max:255'],
            'eu_code' => [
                'required', 'string', 'max:255',
                Rule::unique('banks', 'eu_code')->whereNull('deleted_at')->ignore($bank->id),
            ],
            'supervisor_code' => ['nullable', 'string', 'max:255'],
            'swift' => ['nullable', 'string', 'max:11'],
            'status' => ['sometimes', Rule::enum(BankStatus::class)],
        ];
    }
}
