<?php

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Actions\CreateProduct;
use App\Domains\Catalog\Actions\DeleteProduct;
use App\Domains\Catalog\Actions\UpdateProduct;
use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Enums\FamilyStatus;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\TagStatus;
use App\Domains\Catalog\Http\Requests\ProductTableRequest;
use App\Domains\Catalog\Http\Requests\StoreProductRequest;
use App\Domains\Catalog\Http\Requests\UpdateProductRequest;
use App\Domains\Catalog\Http\Resources\ProductResource;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Family;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Tag;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProductController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Product::class);

        return Inertia::render('Catalog/Products/Index', [
            'family_options' => $this->activeFamilyOptions(),
            'category_options' => $this->activeCategoryOptions(),
            'tag_options' => $this->activeTagOptions(),
        ]);
    }

    public function table(ProductTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::query();

        if ($request->sorts() === []) {
            ActiveFirstOrder::apply($query, ProductStatus::Active, 'name');
        }

        return $table->for($query)
            ->searchable(['name'])
            ->filterUsing('category_id', function (Builder $builder, string $type, mixed $value): void {
                if ($value === null || $value === '') {
                    return;
                }

                $builder->whereHas(
                    'categories',
                    fn (Builder $q) => $q->where('categories.id', (int) $value),
                );
            })
            ->filterUsing('tag_id', function (Builder $builder, string $type, mixed $value): void {
                if ($value === null || $value === '') {
                    return;
                }

                $builder->whereHas(
                    'tags',
                    fn (Builder $q) => $q->where('tags.id', (int) $value),
                );
            })
            ->request($request)
            ->through(ProductResource::class)
            ->json();
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Catalog/Products/Create');
    }

    public function store(StoreProductRequest $request, CreateProduct $action): RedirectResponse
    {
        $product = $action->handle($request->validated());

        return to_route('products.edit', $product)
            ->with('success', __('producto_creado'));
    }

    public function edit(Product $product): InertiaResponse
    {
        $this->authorize('update', $product);

        $product->load(['categories:id', 'tags:id']);

        $images = $product->images()
            ->orderByDesc('featured')
            ->orderBy('id')
            ->get()
            ->map(fn ($image): array => [
                'id' => $image->id,
                'url' => $image->url(),
                'featured' => (bool) $image->featured,
                'public' => (bool) $image->public,
            ])
            ->all();

        $docs = $product->docs()
            ->orderBy('id')
            ->get()
            ->map(fn ($doc): array => [
                'id' => $doc->id,
                'original_name' => $doc->original_name,
                'mime' => $doc->mime,
                'size_bytes' => (int) $doc->size_bytes,
                'public' => (bool) $doc->public,
                'download_url' => $doc->downloadUrl(),
                'created_at' => $doc->created_at?->toIso8601String(),
            ])
            ->all();

        $user = request()->user();
        $canManage = $user?->can('update', $product) ?? false;

        return Inertia::render('Catalog/Products/Edit', [
            'product' => ProductResource::make($product)->resolve(),
            'images' => $images,
            'docs' => $docs,
            'family_options' => $this->familyOptions($product),
            'category_options' => $this->categoryOptions($product),
            'tag_options' => $this->tagOptions($product),
            'can' => [
                'manage_images' => $canManage,
                'manage_docs' => $canManage,
                'create_tags' => $user?->can('create', Tag::class) ?? false,
            ],
            'image_limits' => [
                'max_files' => (int) config('product_images.max_per_product', 20),
                'max_kilobytes' => (int) config('product_images.max_kilobytes', 1024),
            ],
            'doc_limits' => [
                'max_files' => (int) config('product_docs.max_per_product', 30),
                'max_kilobytes' => (int) config('product_docs.max_kilobytes', 20480),
            ],
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProduct $action,
    ): RedirectResponse {
        $action->handle($product, $request->validated());

        return back()->with('success', __('producto_actualizado'));
    }

    public function destroy(Product $product, DeleteProduct $action): RedirectResponse
    {
        $this->authorize('delete', $product);
        $action->handle($product);

        return to_route('products.index')
            ->with('success', __('producto_eliminado'));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function familyOptions(Product $product): array
    {
        $linkedId = $product->family_id;

        return Family::query()
            ->where(function ($query) use ($linkedId): void {
                $query->where('status', FamilyStatus::Active);
                if ($linkedId !== null) {
                    $query->orWhere('id', $linkedId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Family $row): array => [
                'value' => (string) $row->id,
                'label' => $row->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function categoryOptions(Product $product): array
    {
        /** @var Collection<int, int> $linkedIds */
        $linkedIds = $product->categories->pluck('id');

        $all = Category::query()->get(['id', 'name', 'parent_id', 'status']);
        $byId = $all->keyBy('id');

        return $all
            ->filter(fn (Category $row): bool => $row->status === CategoryStatus::Active
                || $linkedIds->contains($row->id))
            ->map(fn (Category $row): array => [
                'value' => (string) $row->id,
                'label' => $this->categoryPathLabel($row, $byId),
            ])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function tagOptions(Product $product): array
    {
        /** @var Collection<int, int> $linkedIds */
        $linkedIds = $product->tags->pluck('id');

        return Tag::query()
            ->where(function ($query) use ($linkedIds): void {
                $query->where('status', TagStatus::Active);
                if ($linkedIds->isNotEmpty()) {
                    $query->orWhereIn('id', $linkedIds);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Tag $row): array => [
                'value' => (string) $row->id,
                'label' => $row->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Category>  $byId
     */
    private function categoryPathLabel(Category $category, Collection $byId): string
    {
        $parts = [$category->name];
        $parentId = $category->parent_id;

        while ($parentId !== null && $byId->has($parentId)) {
            /** @var Category $parent */
            $parent = $byId->get($parentId);
            array_unshift($parts, $parent->name);
            $parentId = $parent->parent_id;
        }

        return implode(' › ', $parts);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function activeFamilyOptions(): array
    {
        return Family::query()
            ->where('status', FamilyStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Family $row): array => [
                'value' => (string) $row->id,
                'label' => $row->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function activeCategoryOptions(): array
    {
        $all = Category::query()
            ->where('status', CategoryStatus::Active)
            ->get(['id', 'name', 'parent_id', 'status']);
        $byId = $all->keyBy('id');

        return $all
            ->map(fn (Category $row): array => [
                'value' => (string) $row->id,
                'label' => $this->categoryPathLabel($row, $byId),
            ])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function activeTagOptions(): array
    {
        return Tag::query()
            ->where('status', TagStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Tag $row): array => [
                'value' => (string) $row->id,
                'label' => $row->name,
            ])
            ->values()
            ->all();
    }
}
