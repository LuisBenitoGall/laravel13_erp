<?php

namespace App\Domains\Catalog\Http\Requests;

use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Models\Category;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Category::class);
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');
        $parentId = $this->input('parent_id');

        $this->merge([
            'status' => is_string($status) && $status !== ''
                ? $status
                : CategoryStatus::Inactive->value,
            'parent_id' => $parentId === '' || $parentId === null ? null : $parentId,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(CategoryStatus::class)],
            'slug' => ['prohibited'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId)->whereNull('deleted_at');
                }),
            ],
        ];
    }
}
