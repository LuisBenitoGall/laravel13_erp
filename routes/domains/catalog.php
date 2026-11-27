<?php

use App\Domains\Catalog\Http\Controllers\CategoryController;
use App\Domains\Catalog\Http\Controllers\FamilyController;
use App\Domains\Catalog\Http\Controllers\ProductController;
use App\Domains\Catalog\Http\Controllers\ProductDocController;
use App\Domains\Catalog\Http\Controllers\ProductImageController;
use App\Domains\Catalog\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Catalog — Module Optional `products` (V-21). Prefijo = module slug.
Route::middleware(['auth', 'company', 'module:products'])
    ->prefix('products')
    ->name('products.')
    ->group(function () {
        // Static segments BEFORE {product}.
        Route::get('families/table', [FamilyController::class, 'table'])->name('families.table');
        Route::get('families', [FamilyController::class, 'index'])->name('families.index');
        Route::get('families/create', [FamilyController::class, 'create'])->name('families.create');
        Route::post('families', [FamilyController::class, 'store'])->name('families.store');
        Route::get('families/{family}/edit', [FamilyController::class, 'edit'])->name('families.edit');
        Route::put('families/{family}', [FamilyController::class, 'update'])->name('families.update');
        Route::delete('families/{family}', [FamilyController::class, 'destroy'])->name('families.destroy');

        Route::get('categories/table', [CategoryController::class, 'table'])->name('categories.table');
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('tags/table', [TagController::class, 'table'])->name('tags.table');
        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::get('tags/create', [TagController::class, 'create'])->name('tags.create');
        Route::post('tags', [TagController::class, 'store'])->name('tags.store');
        Route::post('tags/quick', [TagController::class, 'quickStore'])->name('tags.quick');
        Route::get('tags/{tag}/edit', [TagController::class, 'edit'])->name('tags.edit');
        Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

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
