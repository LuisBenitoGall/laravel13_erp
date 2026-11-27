<?php

namespace App\Domains\Catalog\Http\Requests;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('family_id') && ($this->input('family_id') === '' || $this->input('family_id') === null)) {
            $this->merge(['family_id' => null]);
        }

        if ($this->exists('category_ids')) {
            $raw = $this->input('category_ids');
            $list = is_array($raw) ? $raw : [];
            $this->merge([
                'category_ids' => array_values(array_map('intval', $list)),
            ]);
        }

        if ($this->exists('tag_ids')) {
            $raw = $this->input('tag_ids');
            $list = is_array($raw) ? $raw : [];
            $this->merge([
                'tag_ids' => array_values(array_map('intval', $list)),
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'type' => ['prohibited'],
            'family_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('families', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId)->whereNull('deleted_at');
                }),
            ],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId)->whereNull('deleted_at');
                }),
            ],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => [
                'integer',
                Rule::exists('tags', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId)->whereNull('deleted_at');
                }),
            ],
        ];
    }
}
