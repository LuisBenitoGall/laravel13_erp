<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Workplace;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncWorkplaceDelegationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Workplace $workplace */
        $workplace = $this->route('workplace');

        return $this->user()->can('update', $workplace);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'preferred_delegation_id' => $this->filled('preferred_delegation_id')
                ? $this->input('preferred_delegation_id')
                : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $matrixId = app(CurrentCompany::class)->id();

        return [
            'delegation_ids' => ['present', 'array'],
            'delegation_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('companies', 'id')
                    ->where('parent_id', $matrixId)
                    ->whereNull('deleted_at'),
            ],
            'preferred_delegation_id' => [
                'nullable',
                'integer',
                'in_array:delegation_ids.*',
            ],
        ];
    }
}
