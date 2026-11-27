<?php

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Actions\DeleteProductDoc;
use App\Domains\Catalog\Actions\UpdateProductDocPublic;
use App\Domains\Catalog\Actions\UploadProductDocs;
use App\Domains\Catalog\Http\Requests\StoreProductDocsRequest;
use App\Domains\Catalog\Http\Requests\UpdateProductDocPublicRequest;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDoc;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductDocController extends Controller
{
    public function store(
        StoreProductDocsRequest $request,
        Product $product,
        UploadProductDocs $action,
    ): RedirectResponse {
        $this->authorize('upload', [ProductDoc::class, $product]);

        $action->handle($product, $this->uploadedFiles($request));

        return back()->with('success', __('docs_subidos'));
    }

    public function download(Product $product, ProductDoc $productDoc): StreamedResponse
    {
        abort_unless((int) $productDoc->product_id === (int) $product->id, 404);
        $this->authorize('download', $productDoc);

        return $this->streamDownload($productDoc);
    }

    public function destroy(
        Product $product,
        ProductDoc $productDoc,
        DeleteProductDoc $action,
    ): RedirectResponse {
        abort_unless((int) $productDoc->product_id === (int) $product->id, 404);
        $this->authorize('delete', $productDoc);

        $action->handle($productDoc);

        return back()->with('success', __('doc_eliminado'));
    }

    public function updatePublic(
        UpdateProductDocPublicRequest $request,
        Product $product,
        ProductDoc $productDoc,
        UpdateProductDocPublic $action,
    ): RedirectResponse {
        abort_unless((int) $productDoc->product_id === (int) $product->id, 404);
        $this->authorize('updatePublic', $productDoc);

        $action->handle($productDoc, (bool) $request->validated('public'));

        return back()->with('success', __('doc_public_actualizada'));
    }

    private function streamDownload(ProductDoc $doc): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($doc->path), 404);

        return Storage::disk('local')->download(
            $doc->path,
            $doc->original_name,
            ['Content-Type' => $doc->mime],
        );
    }

    /** @return list<UploadedFile> */
    private function uploadedFiles(StoreProductDocsRequest $request): array
    {
        /** @var list<UploadedFile> $files */
        $files = array_values($request->file('docs', []));

        return $files;
    }
}
