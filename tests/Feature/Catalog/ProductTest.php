<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/CatalogTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('CRUD de productos: Create fuerza Inactive; Update no muta type; soft delete; auditoría', function () {
    $company = Company::factory()->create();
    $user = actingCatalogueUser($company);

    $this->post(route('products.store'), [
        'name' => 'Consultoría',
        'type' => ProductType::Service->value,
    ])->assertRedirect();

    $product = Product::query()->where('name', 'Consultoría')->firstOrFail();

    expect($product->company_id)->toBe($company->id)
        ->and($product->type)->toBe(ProductType::Service)
        ->and($product->status)->toBe(ProductStatus::Inactive)
        ->and($product->created_by)->toBe($user->id)
        ->and($product->updated_by)->toBe($user->id);

    $this->from(route('products.edit', $product))
        ->put(route('products.update', $product), [
            'name' => 'Consultoría premium',
            'status' => ProductStatus::Active->value,
            'type' => ProductType::Physical->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['type']);

    expect($product->fresh()->type)->toBe(ProductType::Service)
        ->and($product->fresh()->status)->toBe(ProductStatus::Inactive);

    $this->put(route('products.update', $product), [
        'name' => 'Consultoría premium',
        'status' => ProductStatus::Active->value,
    ])->assertRedirect();

    $product->refresh();

    expect($product->name)->toBe('Consultoría premium')
        ->and($product->type)->toBe(ProductType::Service)
        ->and($product->status)->toBe(ProductStatus::Active)
        ->and($product->updated_by)->toBe($user->id);

    $this->get(route('products.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Consultoría premium')
        ->assertJsonPath('data.0.type', 'service');

    $this->get(route('products.edit', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Catalog/Products/Edit')
            ->where('product.type', 'service')
            ->where('product.status', 'active'));

    $this->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    expect(Product::query()->whereKey($product->id)->exists())->toBeFalse()
        ->and(Product::withTrashed()->whereKey($product->id)->exists())->toBeTrue();
});

it('Index y Create renderizan páginas Inertia de Catalog', function () {
    actingCatalogueUser(Company::factory()->create());

    $this->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Catalog/Products/Index'));

    $this->get(route('products.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Catalog/Products/Create'));
});

it('valida name y type en store; status prohibited', function () {
    actingCatalogueUser(Company::factory()->create());

    $this->from(route('products.create'))
        ->post(route('products.store'), [
            'name' => '',
            'type' => 'widget',
            'status' => 'active',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['name', 'type', 'status']);
});

it('aísla productos entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $productA = Product::factory()->create(['company_id' => $companyA->id, 'name' => 'Prod A']);
    $productB = Product::factory()->create(['company_id' => $companyB->id, 'name' => 'Prod B']);

    actingCatalogueUser($companyA);

    expect(Product::query()->pluck('id')->all())
        ->toContain($productA->id)
        ->not->toContain($productB->id);

    $this->get(route('products.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Prod A');

    $this->get(route('products.edit', $productB))->assertNotFound();
    $this->put(route('products.update', $productB), [
        'name' => 'Hacked',
        'status' => 'active',
    ])->assertNotFound();
    $this->delete(route('products.destroy', $productB))->assertNotFound();

    expect($productB->fresh()->name)->toBe('Prod B');
});

it('responde 403 sin permiso de products', function () {
    $company = Company::factory()->create();
    $product = Product::factory()->create(['company_id' => $company->id]);

    actingCatalogueUser($company, ['products.access']);

    $this->get(route('products.index'))->assertForbidden();
    $this->get(route('products.table'))->assertForbidden();
    $this->get(route('products.create'))->assertForbidden();
    $this->post(route('products.store'), [
        'name' => 'X',
        'type' => 'physical',
    ])->assertForbidden();
    $this->get(route('products.edit', $product))->assertForbidden();
    $this->put(route('products.update', $product), [
        'name' => 'X',
        'status' => 'active',
    ])->assertForbidden();
    $this->delete(route('products.destroy', $product))->assertForbidden();
});
