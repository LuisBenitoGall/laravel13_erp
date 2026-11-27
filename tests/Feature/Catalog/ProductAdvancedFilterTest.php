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
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/CatalogTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('Index de productos expone opciones Active para filtros avanzados', function () {
    $company = Company::factory()->create();
    actingCatalogueUser($company);

    $family = Family::factory()->create([
        'company_id' => $company->id,
        'status' => FamilyStatus::Active,
        'name' => 'Fam Filtro',
    ]);
    Family::factory()->create([
        'company_id' => $company->id,
        'status' => FamilyStatus::Inactive,
        'name' => 'Fam Off',
    ]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Catalog/Products/Index')
            ->has('family_options', 1)
            ->where('family_options.0.value', (string) $family->id)
            ->has('category_options')
            ->has('tag_options'));
});

it('table filtra por family_id, category_id y tag_id', function () {
    $company = Company::factory()->create();
    actingCatalogueUser($company);

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

    $match = Product::factory()->create([
        'company_id' => $company->id,
        'name' => 'Match Tax',
        'family_id' => $family->id,
        'status' => ProductStatus::Active,
    ]);
    $match->categories()->sync([$category->id]);
    $match->tags()->sync([$tag->id]);

    Product::factory()->create([
        'company_id' => $company->id,
        'name' => 'Other',
        'status' => ProductStatus::Active,
    ]);

    $filter = json_encode([
        ['field' => 'family_id', 'type' => 'eq', 'value' => (string) $family->id],
        ['field' => 'category_id', 'type' => 'eq', 'value' => (string) $category->id],
        ['field' => 'tag_id', 'type' => 'eq', 'value' => (string) $tag->id],
    ], JSON_THROW_ON_ERROR);

    $this->get(route('products.table', ['filter' => $filter]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $match->id)
        ->assertJsonPath('data.0.name', 'Match Tax');
});
