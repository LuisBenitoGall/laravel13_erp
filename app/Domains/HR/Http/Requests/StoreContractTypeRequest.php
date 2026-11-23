<?php

namespace App\Domains\HR\Http\Requests;

use App\Domains\HR\Enums\ContractTypeStatus;
use App\Domains\HR\Models\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ContractType::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');
        $durationInput = $this->input('duration');
        // Indefinido explícito, o duration ausente/null/'' (no interpretar 0 como vacío).
        $indefinite = $this->has('indefinite')
            ? $this->boolean('indefinite')
            : ($durationInput === null || $durationInput === '');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : ContractTypeStatus::Active->value,
            'with_holidays' => $this->boolean('with_holidays'),
            'indefinite' => $indefinite,
            'duration' => $indefinite ? null : $durationInput,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'indefinite' => ['boolean'],
            // Indefinido = duration null; temporal exige ≥ 1 día (nunca 0).
            'duration' => [
                Rule::requiredIf(fn (): bool => ! $this->boolean('indefinite')),
                'nullable',
                'integer',
                'min:1',
                'max:36500',
            ],
            'with_holidays' => ['boolean'],
            'status' => ['required', Rule::enum(ContractTypeStatus::class)],
        ];
    }
}
