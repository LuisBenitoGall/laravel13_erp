<?php

namespace App\Domains\Catalog\Http\Requests;

use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Models\Category;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('category'));
    }

    protected function prepareForValidation(): void
    {
        $parentId = $this->input('parent_id');

        $this->merge([
            'parent_id' => $parentId === '' || $parentId === null ? null : $parentId,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $companyId = app(CurrentCompany::class)->id();
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(CategoryStatus::class)],
            'slug' => ['prohibited'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId, $category): void {
                    $query->where('company_id', $companyId)
                        ->whereNull('deleted_at')
                        ->where('id', '!=', $category->id);
                }),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $parentId = $this->input('parent_id');
            if ($parentId === null || $parentId === '') {
                return;
            }

            /** @var Category $category */
            $category = $this->route('category');
            $parent = Category::query()->find((int) $parentId);

            if ($parent === null || $category->wouldCycleWithParent($parent)) {
                $validator->errors()->add('parent_id', __('categoria_ciclo_rechazado'));
            }
        });
    }
}
