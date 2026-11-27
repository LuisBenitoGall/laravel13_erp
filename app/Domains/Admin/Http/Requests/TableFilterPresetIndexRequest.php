<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\TableFilterPreset;
use Illuminate\Foundation\Http\FormRequest;

class TableFilterPresetIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', TableFilterPreset::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'table_key' => ['required', 'string', 'max:120'],
        ];
    }
}
