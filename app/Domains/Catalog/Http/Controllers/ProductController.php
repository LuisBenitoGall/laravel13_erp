<?php

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Actions\CreateProduct;
use App\Domains\Catalog\Actions\DeleteProduct;
use App\Domains\Catalog\Actions\UpdateProduct;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Http\Requests\ProductTableRequest;
use App\Domains\Catalog\Http\Requests\StoreProductRequest;
use App\Domains\Catalog\Http\Requests\UpdateProductRequest;
use App\Domains\Catalog\Http\Resources\ProductResource;
use App\Domains\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Support\Table\ActiveFirstOrder;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProductController extends Controller
{
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Product::class);

        return Inertia::render('Catalog/Products/Index');
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

        $canManage = request()->user()?->can('update', $product) ?? false;

        return Inertia::render('Catalog/Products/Edit', [
            'product' => ProductResource::make($product)->resolve(),
            'images' => $images,
            'docs' => $docs,
            'can' => [
                'manage_images' => $canManage,
                'manage_docs' => $canManage,
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
}
