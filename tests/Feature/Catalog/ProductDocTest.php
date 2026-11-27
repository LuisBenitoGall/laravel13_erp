<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDoc;
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

it('expone rutas products.docs.*', function () {
    expect(Route::has('products.docs.store'))->toBeTrue()
        ->and(Route::has('products.docs.download'))->toBeTrue()
        ->and(Route::has('products.docs.destroy'))->toBeTrue()
        ->and(Route::has('products.docs.public'))->toBeTrue();
});

it('sube toggle public y elimina; descarga autenticada; Edit muestra tab docs', function () {
    $company = Company::factory()->create();
    $user = actingCatalogueUser($company);
    $product = Product::factory()->create(['company_id' => $company->id]);

    $file = UploadedFile::fake()->create('ficha.pdf', 200, 'application/pdf');

    $this->post(route('products.docs.store', $product), [
        'docs' => [$file],
    ])->assertRedirect();

    $doc = ProductDoc::query()->where('product_id', $product->id)->first();
    expect($doc)->not->toBeNull()
        ->and($doc->company_id)->toBe($company->id)
        ->and($doc->public)->toBeFalse()
        ->and($doc->original_name)->toBe('ficha.pdf')
        ->and(Storage::disk('local')->exists($doc->path))->toBeTrue()
        ->and($doc->downloadUrl())->not->toContain('/storage/')
        ->and($doc->downloadUrl())->toContain('/products/'.$product->id.'/docs/');

    $this->patch(route('products.docs.public', [$product, $doc]), ['public' => true])->assertRedirect();
    expect($doc->fresh()->public)->toBeTrue();

    $this->get(route('products.edit', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Products/Edit')
            ->has('docs', 1)
            ->where('docs.0.download_url', $doc->downloadUrl())
            ->where('can.manage_docs', true)
            ->where('doc_limits.max_files', 30));

    $this->get(route('products.docs.download', [$product, $doc]))
        ->assertOk()
        ->assertHeader('content-disposition');

    $path = $doc->path;
    $this->delete(route('products.docs.destroy', [$product, $doc]))->assertRedirect();
    expect(ProductDoc::query()->whereKey($doc->id)->exists())->toBeFalse()
        ->and(ProductDoc::withTrashed()->whereKey($doc->id)->exists())->toBeTrue()
        ->and(Storage::disk('local')->exists($path))->toBeFalse();
});

it('rechaza formato peso y limite 30', function () {
    $company = Company::factory()->create();
    actingCatalogueUser($company);
    $product = Product::factory()->create(['company_id' => $company->id]);

    $this->post(route('products.docs.store', $product), [
        'docs' => [UploadedFile::fake()->image('photo.jpg', 40, 40)->size(50)],
    ])->assertInvalid(['docs.0']);

    $this->post(route('products.docs.store', $product), [
        'docs' => [UploadedFile::fake()->create('big.pdf', 30_000, 'application/pdf')],
    ])->assertInvalid(['docs.0']);

    for ($i = 0; $i < 30; $i++) {
        ProductDoc::factory()->forProduct($product)->create([
            'path' => sprintf('companies/%d/products/%d/docs/seed-%d.pdf', $company->id, $product->id, $i),
        ]);
    }

    $this->post(route('products.docs.store', $product), [
        'docs' => [UploadedFile::fake()->create('overflow.pdf', 50, 'application/pdf')],
    ])->assertInvalid(['docs']);
});

it('guest no descarga; editor descarga no-pública', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);
    $doc = ProductDoc::factory()->forProduct($product)->create([
        'public' => false,
        'path' => sprintf('companies/%d/products/%d/docs/secret.pdf', $company->id, $product->id),
    ]);
    Storage::disk('local')->put($doc->path, 'fake-pdf-bytes');

    $this->get(route('products.docs.download', [$product, $doc]))
        ->assertRedirect();

    actingCatalogueUser($company);
    $this->get(route('products.docs.download', [$product, $doc]))->assertOk();
});

it('aísla documentos entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $productB = Product::factory()->create(['company_id' => $companyB->id]);
    $docB = ProductDoc::factory()->forProduct($productB)->create();

    actingCatalogueUser($companyA);

    $this->post(route('products.docs.store', $productB), [
        'docs' => [UploadedFile::fake()->create('x.pdf', 20, 'application/pdf')],
    ])->assertNotFound();

    $this->get(route('products.docs.download', [$productB, $docB]))->assertNotFound();
    $this->delete(route('products.docs.destroy', [$productB, $docB]))->assertNotFound();
    $this->patch(route('products.docs.public', [$productB, $docB]), ['public' => true])->assertNotFound();
});

it('responde 403 sin permiso catalogue.update', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);
    $doc = ProductDoc::factory()->forProduct($product)->create();

    actingCatalogueUser($company, ['products.access', 'catalogue.view', 'catalogue.viewAny']);

    $this->post(route('products.docs.store', $product), [
        'docs' => [UploadedFile::fake()->create('x.pdf', 20, 'application/pdf')],
    ])->assertForbidden();

    $this->get(route('products.docs.download', [$product, $doc]))->assertForbidden();
    $this->delete(route('products.docs.destroy', [$product, $doc]))->assertForbidden();
    $this->patch(route('products.docs.public', [$product, $doc]), ['public' => true])
        ->assertForbidden();
});

it('mensaje de límite 30 interpola :max', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);
    actingCatalogueUser($company);

    $max = (int) config('product_docs.max_per_product', 30);
    ProductDoc::factory()->forProduct($product)->count($max)->create();

    $this->from(route('products.edit', $product))
        ->post(route('products.docs.store', $product), [
            'docs' => [UploadedFile::fake()->create('over.pdf', 20, 'application/pdf')],
        ])
        ->assertSessionHasErrors('docs');

    $errors = session('errors');
    expect($errors)->not->toBeNull()
        ->and($errors->first('docs'))->toContain((string) $max)
        ->and($errors->first('docs'))->not->toContain('{{max}}');
});
