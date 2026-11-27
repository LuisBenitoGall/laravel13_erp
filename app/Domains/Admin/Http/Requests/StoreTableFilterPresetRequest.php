<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\TableFilterPreset;
use App\Support\Table\TableRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTableFilterPresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', TableFilterPreset::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'table_key' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'filters' => ['required', 'array'],
            'filters.*.field' => ['required', 'string', 'max:120'],
            'filters.*.type' => ['required', 'string', Rule::in(TableRequest::ALLOWED_FILTER_TYPES)],
            'filters.*.value' => ['nullable'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $user = $this->user();
            $tableKey = (string) $this->input('table_key');
            $name = trim((string) $this->input('name'));

            $count = TableFilterPreset::query()
                ->where('user_id', $user->id)
                ->where('table_key', $tableKey)
                ->count();

            if ($count >= TableFilterPreset::MAX_PER_TABLE) {
                $validator->errors()->add(
                    'name',
                    __('presets_filtro_limite_alcanzado', [
                        'max' => TableFilterPreset::MAX_PER_TABLE,
                    ]),
                );

                return;
            }

            $duplicate = TableFilterPreset::query()
                ->where('user_id', $user->id)
                ->where('table_key', $tableKey)
                ->where('name', $name)
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('name', __('presets_filtro_nombre_duplicado'));
            }
        });
    }
}
