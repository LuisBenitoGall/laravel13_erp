<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\SectorStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyDirectoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAnyCompanyDirectory') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $sectorIds = $this->input('sector_ids', []);
        if (! is_array($sectorIds)) {
            $sectorIds = $sectorIds !== null && $sectorIds !== '' ? [$sectorIds] : [];
        }

        $this->merge([
            'sector_ids' => array_values(array_filter(
                array_map('intval', $sectorIds),
                fn (int $id): bool => $id > 0,
            )),
            'sort' => is_string($this->input('sort')) && $this->input('sort') !== ''
                ? $this->input('sort')
                : 'name_asc',
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sector_ids' => ['sometimes', 'array'],
            'sector_ids.*' => [
                'integer',
                Rule::exists('sectors', 'id')
                    ->whereNull('deleted_at')
                    ->where('status', SectorStatus::Active->value),
            ],
            'sort' => ['sometimes', 'string', Rule::in(['name_asc', 'name_desc', 'recent'])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
