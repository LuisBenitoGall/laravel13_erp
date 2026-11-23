<?php

namespace App\Domains\HR\Http\Requests;

use App\Domains\HR\Enums\ContractTypeStatus;
use App\Domains\HR\Models\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ContractType $contractType */
        $contractType = $this->route('contract_type');

        return $this->user()->can('update', $contractType);
    }

    protected function prepareForValidation(): void
    {
        $durationInput = $this->input('duration');
        $indefinite = $this->has('indefinite')
            ? $this->boolean('indefinite')
            : ($durationInput === null || $durationInput === '');

        $this->merge([
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
