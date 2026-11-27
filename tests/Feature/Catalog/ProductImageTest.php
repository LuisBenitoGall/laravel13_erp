<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/CatalogTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('expone rutas products.images.*', function () {
    expect(Route::has('products.images.store'))->toBeTrue()
        ->and(Route::has('products.images.file'))->toBeTrue()
        ->and(Route::has('products.images.destroy'))->toBeTrue()
        ->and(Route::has('products.images.featured'))->toBeTrue()
        ->and(Route::has('products.images.public'))->toBeTrue();
});

it('sube marca featured toggle public y elimina; URL autenticada; Edit muestra tab', function () {
    $company = Company::factory()->create();
    $user = actingCatalogueUser($company);
    $product = Product::factory()->create(['company_id' => $company->id]);

    $file = UploadedFile::fake()->image('cover.jpg', 100, 100)->size(200);

    $this->post(route('products.images.store', $product), [
        'images' => [$file],
    ])->assertRedirect();

    $image = ProductImage::query()->where('product_id', $product->id)->first();
    expect($image)->not->toBeNull()
        ->and($image->company_id)->toBe($company->id)
        ->and($image->featured)->toBeFalse()
        ->and($image->public)->toBeFalse()
        ->and(Storage::disk('local')->exists($image->image))->toBeTrue()
        ->and($image->url())->not->toContain('/storage/')
        ->and($image->url())->toContain('/products/'.$product->id.'/images/');

    $this->post(route('products.images.featured', [$product, $image]))->assertRedirect();
    expect($image->fresh()->featured)->toBeTrue();

    $second = UploadedFile::fake()->image('second.png', 80, 80)->size(100);
    $this->post(route('products.images.store', $product), ['images' => [$second]])->assertRedirect();
    $other = ProductImage::query()->where('product_id', $product->id)->whereKeyNot($image->id)->firstOrFail();

    $this->post(route('products.images.featured', [$product, $other]))->assertRedirect();
    expect($other->fresh()->featured)->toBeTrue()
        ->and($image->fresh()->featured)->toBeFalse();

    $this->patch(route('products.images.public', [$product, $other]), ['public' => true])->assertRedirect();
    expect($other->fresh()->public)->toBeTrue();

    $this->get(route('products.edit', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Products/Edit')
            ->has('images', 2)
            ->where('can.manage_images', true));

    $this->get(route('products.images.file', [$product, $other]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');

    $path = $other->image;
    $this->delete(route('products.images.destroy', [$product, $other]))->assertRedirect();
    expect(ProductImage::query()->whereKey($other->id)->exists())->toBeFalse()
        ->and(ProductImage::withTrashed()->whereKey($other->id)->exists())->toBeTrue()
        ->and(Storage::disk('local')->exists($path))->toBeFalse();
});

it('rechaza formato peso y limite 20', function () {
    $company = Company::factory()->create();
    actingCatalogueUser($company);
    $product = Product::factory()->create(['company_id' => $company->id]);

    $this->post(route('products.images.store', $product), [
        'images' => [UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')],
    ])->assertInvalid(['images.0']);

    $this->post(route('products.images.store', $product), [
        'images' => [UploadedFile::fake()->image('big.jpg')->size(2048)],
    ])->assertInvalid(['images.0']);

    for ($i = 0; $i < 20; $i++) {
        ProductImage::factory()->forProduct($product)->create([
            'image' => sprintf('companies/%d/products/%d/images/seed-%d.jpg', $company->id, $product->id, $i),
        ]);
    }

    $this->post(route('products.images.store', $product), [
        'images' => [UploadedFile::fake()->image('overflow.jpg', 40, 40)->size(50)],
    ])->assertInvalid(['images']);
});

it('guest no obtiene binario; editor obtiene no-pública', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);
    $image = ProductImage::factory()->forProduct($product)->create([
        'public' => false,
        'image' => sprintf('companies/%d/products/%d/images/secret.jpg', $company->id, $product->id),
    ]);
    Storage::disk('local')->put($image->image, 'fake-bytes');

    $this->get(route('products.images.file', [$product, $image]))
        ->assertRedirect();

    actingCatalogueUser($company);
    $this->get(route('products.images.file', [$product, $image]))->assertOk();
});

it('aísla imágenes entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $productB = Product::factory()->create(['company_id' => $companyB->id]);
    $imageB = ProductImage::factory()->forProduct($productB)->create();

    actingCatalogueUser($companyA);

    $this->post(route('products.images.store', $productB), [
        'images' => [UploadedFile::fake()->image('x.jpg', 20, 20)->size(20)],
    ])->assertNotFound();

    $this->get(route('products.images.file', [$productB, $imageB]))->assertNotFound();
    $this->delete(route('products.images.destroy', [$productB, $imageB]))->assertNotFound();
    $this->post(route('products.images.featured', [$productB, $imageB]))->assertNotFound();
    $this->patch(route('products.images.public', [$productB, $imageB]), ['public' => true])->assertNotFound();
});

it('responde 403 sin permiso catalogue.update', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);
    $image = ProductImage::factory()->forProduct($product)->create();

    actingCatalogueUser($company, ['products.access', 'catalogue.view', 'catalogue.viewAny']);

    $this->post(route('products.images.store', $product), [
        'images' => [UploadedFile::fake()->image('x.jpg', 20, 20)->size(20)],
    ])->assertForbidden();

    $this->get(route('products.images.file', [$product, $image]))->assertForbidden();
    $this->delete(route('products.images.destroy', [$product, $image]))->assertForbidden();
    $this->post(route('products.images.featured', [$product, $image]))->assertForbidden();
    $this->patch(route('products.images.public', [$product, $image]), ['public' => true])
        ->assertForbidden();
});

it('mensaje de límite 20 interpola :max', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);
    actingCatalogueUser($company);

    $max = (int) config('product_images.max_per_product', 20);
    ProductImage::factory()->forProduct($product)->count($max)->create();

    $this->from(route('products.edit', $product))
        ->post(route('products.images.store', $product), [
            'images' => [UploadedFile::fake()->image('over.jpg', 20, 20)->size(20)],
        ])
        ->assertSessionHasErrors('images');

    $errors = session('errors');
    expect($errors)->not->toBeNull()
        ->and($errors->first('images'))->toContain((string) $max)
        ->and($errors->first('images'))->not->toContain('{{max}}');
});
