<?php

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Actions\CreateCategory;
use App\Domains\Catalog\Actions\DeleteCategory;
use App\Domains\Catalog\Actions\UpdateCategory;
use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Http\Requests\CategoryTableRequest;
use App\Domains\Catalog\Http\Requests\StoreCategoryRequest;
use App\Domains\Catalog\Http\Requests\UpdateCategoryRequest;
use App\Domains\Catalog\Http\Resources\CategoryResource;
use App\Domains\Catalog\Models\Category;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CategoryController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Category::class);

        return Inertia::render('Catalog/Categories/Index');
    }

    public function table(CategoryTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        $query = Category::query()->with('parent:id,name');

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, CategoryStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name', 'slug'])
            ->request($request)
            ->through(CategoryResource::class)
            ->json();
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Catalog/Categories/Create', [
            'parent_options' => $this->parentOptions(),
        ]);
    }

    public function store(StoreCategoryRequest $request, CreateCategory $action): RedirectResponse
    {
        $category = $action->handle($request->validated());

        return to_route('products.categories.edit', $category)
            ->with('success', __('categoria_creada'));
    }

    public function edit(Category $category): InertiaResponse
    {
        $this->authorize('update', $category);

        return Inertia::render('Catalog/Categories/Edit', [
            'category' => CategoryResource::make($category->load('parent:id,name'))->resolve(),
            'parent_options' => $this->parentOptions($category),
        ]);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategory $action,
    ): RedirectResponse {
        $action->handle($category, $request->validated());

        return back()->with('success', __('categoria_actualizada'));
    }

    public function destroy(Category $category, DeleteCategory $action): RedirectResponse
    {
        $this->authorize('delete', $category);
        $action->handle($category);

        return to_route('products.categories.index')
            ->with('success', __('categoria_eliminada'));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function parentOptions(?Category $exclude = null): array
    {
        $excluded = collect();

        if ($exclude !== null) {
            $excluded = $exclude->descendantIds()->push($exclude->id);
        }

        return Category::query()
            ->when($excluded->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $excluded))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $row): array => [
                'value' => (string) $row->id,
                'label' => $row->name,
            ])
            ->values()
            ->all();
    }
}
