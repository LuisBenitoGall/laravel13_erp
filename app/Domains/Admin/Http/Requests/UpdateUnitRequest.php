<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\UnitStatus;
use App\Domains\Admin\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('unit'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Unit $unit */
        $unit = $this->route('unit');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('units', 'name')->whereNull('deleted_at')->ignore($unit->id),
            ],
            'symbol' => ['nullable', 'string', 'max:4'],
            'status' => ['sometimes', Rule::enum(UnitStatus::class)],
        ];
    }
}
