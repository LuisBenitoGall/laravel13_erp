<?php

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Actions\DeleteProductImage;
use App\Domains\Catalog\Actions\MarkProductImageFeatured;
use App\Domains\Catalog\Actions\UpdateProductImagePublic;
use App\Domains\Catalog\Actions\UploadProductImages;
use App\Domains\Catalog\Http\Requests\StoreProductImagesRequest;
use App\Domains\Catalog\Http\Requests\UpdateProductImagePublicRequest;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductImageController extends Controller
{
    public function store(
        StoreProductImagesRequest $request,
        Product $product,
        UploadProductImages $action,
    ): RedirectResponse {
        $this->authorize('upload', [ProductImage::class, $product]);

        $action->handle($product, $this->uploadedFiles($request));

        return back()->with('success', __('imagenes_subidas'));
    }

    public function file(Product $product, ProductImage $productImage): BinaryFileResponse
    {
        abort_unless((int) $productImage->product_id === (int) $product->id, 404);
        $this->authorize('view', $productImage);

        return $this->streamFile($productImage);
    }

    public function destroy(
        Product $product,
        ProductImage $productImage,
        DeleteProductImage $action,
    ): RedirectResponse {
        abort_unless((int) $productImage->product_id === (int) $product->id, 404);
        $this->authorize('delete', $productImage);

        $action->handle($productImage);

        return back()->with('success', __('imagen_eliminada'));
    }

    public function markFeatured(
        Product $product,
        ProductImage $productImage,
        MarkProductImageFeatured $action,
    ): RedirectResponse {
        abort_unless((int) $productImage->product_id === (int) $product->id, 404);
        $this->authorize('markFeatured', $productImage);

        $action->handle($productImage);

        return back()->with('success', __('imagen_marcada_featured'));
    }

    public function updatePublic(
        UpdateProductImagePublicRequest $request,
        Product $product,
        ProductImage $productImage,
        UpdateProductImagePublic $action,
    ): RedirectResponse {
        abort_unless((int) $productImage->product_id === (int) $product->id, 404);
        $this->authorize('updatePublic', $productImage);

        $action->handle($productImage, (bool) $request->validated('public'));

        return back()->with('success', __('imagen_public_actualizada'));
    }

    private function streamFile(ProductImage $image): BinaryFileResponse
    {
        abort_unless(Storage::disk('local')->exists($image->image), 404);

        $absolute = Storage::disk('local')->path($image->image);
        $mime = mime_content_type($absolute) ?: 'application/octet-stream';

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /** @return list<UploadedFile> */
    private function uploadedFiles(StoreProductImagesRequest $request): array
    {
        /** @var list<UploadedFile> $files */
        $files = array_values($request->file('images', []));

        return $files;
    }
}
