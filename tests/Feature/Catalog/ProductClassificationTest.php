<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Enums\FamilyStatus;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\TagStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Family;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/CatalogTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('expone ruta products.tags.quick', function () {
    expect(Route::has('products.tags.quick'))->toBeTrue();
});

it('asigna y limpia familia, categorías y tags en Update', function () {
    $company = Company::factory()->create();
    actingCatalogueUser($company);

    $product = Product::factory()->create(['company_id' => $company->id]);
    $family = Family::factory()->create([
        'company_id' => $company->id,
        'status' => FamilyStatus::Active,
    ]);
    $catA = Category::factory()->create([
        'company_id' => $company->id,
        'status' => CategoryStatus::Active,
        'name' => 'Cat A',
    ]);
    $catB = Category::factory()->create([
        'company_id' => $company->id,
        'status' => CategoryStatus::Active,
        'name' => 'Cat B',
    ]);
    $tagA = Tag::factory()->create([
        'company_id' => $company->id,
        'status' => TagStatus::Active,
        'name' => 'Tag A',
    ]);
    $tagB = Tag::factory()->create([
        'company_id' => $company->id,
        'status' => TagStatus::Active,
        'name' => 'Tag B',
    ]);

    $this->put(route('products.update', $product), [
        'name' => $product->name,
        'status' => ProductStatus::Active->value,
        'family_id' => $family->id,
        'category_ids' => [$catA->id, $catB->id],
        'tag_ids' => [$tagA->id, $tagB->id],
    ])->assertRedirect();

    $product->refresh();

    expect($product->family_id)->toBe($family->id)
        ->and($product->categories()->pluck('categories.id')->sort()->values()->all())
        ->toBe([$catA->id, $catB->id])
        ->and($product->tags()->pluck('tags.id')->sort()->values()->all())
        ->toBe([$tagA->id, $tagB->id]);

    $this->put(route('products.update', $product), [
        'name' => $product->name,
        'status' => ProductStatus::Active->value,
        'family_id' => null,
        'category_ids' => [],
        'tag_ids' => [],
    ])->assertRedirect();

    $product->refresh();

    expect($product->family_id)->toBeNull()
        ->and($product->categories()->count())->toBe(0)
        ->and($product->tags()->count())->toBe(0);
});

it('rechaza familia, categoría o tag de otra company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    actingCatalogueUser($companyA);

    $product = Product::factory()->create(['company_id' => $companyA->id]);
    $familyB = Family::factory()->create(['company_id' => $companyB->id, 'status' => FamilyStatus::Active]);
    $categoryB = Category::factory()->create(['company_id' => $companyB->id, 'status' => CategoryStatus::Active]);
    $tagB = Tag::factory()->create(['company_id' => $companyB->id, 'status' => TagStatus::Active]);

    $this->from(route('products.edit', $product))
        ->put(route('products.update', $product), [
            'name' => $product->name,
            'status' => ProductStatus::Inactive->value,
            'family_id' => $familyB->id,
            'category_ids' => [],
            'tag_ids' => [],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['family_id']);

    $this->from(route('products.edit', $product))
        ->put(route('products.update', $product), [
            'name' => $product->name,
            'status' => ProductStatus::Inactive->value,
            'family_id' => null,
            'category_ids' => [$categoryB->id],
            'tag_ids' => [],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['category_ids.0']);

    $this->from(route('products.edit', $product))
        ->put(route('products.update', $product), [
            'name' => $product->name,
            'status' => ProductStatus::Inactive->value,
            'family_id' => null,
            'category_ids' => [],
            'tag_ids' => [$tagB->id],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['tag_ids.0']);
});

it('Edit incluye opciones Active y can.create_tags; Create sin clasificación', function () {
    $company = Company::factory()->create();
    ensureTagsCatalog();
    $user = actingCatalogueUser($company, array_values(array_unique([
        ...cataloguePermissions(),
        ...tagsPermissions(),
    ])));

    $product = Product::factory()->create(['company_id' => $company->id]);
    $familyActive = Family::factory()->create([
        'company_id' => $company->id,
        'status' => FamilyStatus::Active,
        'name' => 'Fam Activa',
    ]);
    Family::factory()->create([
        'company_id' => $company->id,
        'status' => FamilyStatus::Inactive,
        'name' => 'Fam Inactiva',
    ]);
    $tagActive = Tag::factory()->create([
        'company_id' => $company->id,
        'status' => TagStatus::Active,
        'name' => 'Tag Activo',
    ]);

    $this->get(route('products.edit', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Catalog/Products/Edit')
            ->where('can.create_tags', true)
            ->has('family_options', 1)
            ->where('family_options.0.value', (string) $familyActive->id)
            ->has('tag_options', 1)
            ->where('tag_options.0.value', (string) $tagActive->id)
            ->where('product.family_id', null)
            ->where('product.category_ids', [])
            ->where('product.tag_ids', []));

    $this->get(route('products.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Catalog/Products/Create')
            ->missing('family_options')
            ->missing('category_options')
            ->missing('tag_options'));

    expect($user->can('create', Tag::class))->toBeTrue();
});

it('quick-create tag fuerza Active con tags.create y 403 sin permiso', function () {
    $company = Company::factory()->create();
    ensureTagsCatalog();
    actingCatalogueUser($company, array_values(array_unique([
        ...cataloguePermissions(),
        ...tagsPermissions(),
    ])));

    $this->postJson(route('products.tags.quick'), ['name' => 'Flash'])
        ->assertCreated()
        ->assertJsonPath('name', 'Flash')
        ->assertJsonPath('status', TagStatus::Active->value);

    $tag = Tag::query()->where('name', 'Flash')->firstOrFail();
    expect($tag->company_id)->toBe($company->id)
        ->and($tag->status)->toBe(TagStatus::Active);

    // Solo catalogue (sin tags.create)
    actingCatalogueUser($company);

    $this->postJson(route('products.tags.quick'), ['name' => 'Denied'])
        ->assertForbidden();

    expect(Tag::query()->where('name', 'Denied')->exists())->toBeFalse();
});

it('PUT parcial sin claves de clasificación no borra vínculos', function () {
    $company = Company::factory()->create();
    actingCatalogueUser($company);

    $product = Product::factory()->create(['company_id' => $company->id]);
    $family = Family::factory()->create([
        'company_id' => $company->id,
        'status' => FamilyStatus::Active,
    ]);
    $category = Category::factory()->create([
        'company_id' => $company->id,
        'status' => CategoryStatus::Active,
    ]);
    $tag = Tag::factory()->create([
        'company_id' => $company->id,
        'status' => TagStatus::Active,
    ]);

    $product->update(['family_id' => $family->id]);
    $product->categories()->sync([$category->id]);
    $product->tags()->sync([$tag->id]);

    $this->put(route('products.update', $product), [
        'name' => 'Solo nombre',
        'status' => ProductStatus::Active->value,
    ])->assertRedirect();

    $product->refresh();

    expect($product->name)->toBe('Solo nombre')
        ->and($product->status)->toBe(ProductStatus::Active)
        ->and($product->family_id)->toBe($family->id)
        ->and($product->categories()->pluck('categories.id')->all())->toBe([$category->id])
        ->and($product->tags()->pluck('tags.id')->all())->toBe([$tag->id]);
});

it('aísla clasificación entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $productA = Product::factory()->create(['company_id' => $companyA->id]);
    $familyA = Family::factory()->create(['company_id' => $companyA->id, 'status' => FamilyStatus::Active]);
    $categoryA = Category::factory()->create(['company_id' => $companyA->id, 'status' => CategoryStatus::Active]);
    $tagA = Tag::factory()->create(['company_id' => $companyA->id, 'status' => TagStatus::Active]);

    $productA->update(['family_id' => $familyA->id]);
    $productA->categories()->sync([$categoryA->id]);
    $productA->tags()->sync([$tagA->id]);

    actingCatalogueUser($companyB);

    expect(Product::query()->whereKey($productA->id)->exists())->toBeFalse();

    $this->put(route('products.update', $productA), [
        'name' => 'Hack',
        'status' => ProductStatus::Active->value,
        'family_id' => null,
        'category_ids' => [],
        'tag_ids' => [],
    ])->assertNotFound();
});
