<?php

use App\Domains\Catalog\Http\Controllers\ProductController;
use App\Domains\Catalog\Http\Controllers\ProductDocController;
use App\Domains\Catalog\Http\Controllers\ProductImageController;
use Illuminate\Support\Facades\Route;

// Catalog — Module Optional `products` (V-21). Prefijo = module slug.
Route::middleware(['auth', 'company', 'module:products'])
    ->prefix('products')
    ->name('products.')
    ->group(function () {
        Route::get('table', [ProductController::class, 'table'])->name('table');
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');

        Route::post('{product}/images', [ProductImageController::class, 'store'])->name('images.store');
        Route::get('{product}/images/{productImage}/file', [ProductImageController::class, 'file'])->name('images.file');
        Route::delete('{product}/images/{productImage}', [ProductImageController::class, 'destroy'])->name('images.destroy');
        Route::post('{product}/images/{productImage}/featured', [ProductImageController::class, 'markFeatured'])->name('images.featured');
        Route::patch('{product}/images/{productImage}/public', [ProductImageController::class, 'updatePublic'])->name('images.public');

        Route::post('{product}/docs', [ProductDocController::class, 'store'])->name('docs.store');
        Route::get('{product}/docs/{productDoc}/download', [ProductDocController::class, 'download'])->name('docs.download');
        Route::delete('{product}/docs/{productDoc}', [ProductDocController::class, 'destroy'])->name('docs.destroy');
        Route::patch('{product}/docs/{productDoc}/public', [ProductDocController::class, 'updatePublic'])->name('docs.public');
    });
