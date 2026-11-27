<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/CatalogTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('expone rutas products.categories.*', function () {
    expect(Route::has('products.categories.index'))->toBeTrue()
        ->and(Route::has('products.categories.table'))->toBeTrue()
        ->and(Route::has('products.categories.create'))->toBeTrue()
        ->and(Route::has('products.categories.store'))->toBeTrue()
        ->and(Route::has('products.categories.edit'))->toBeTrue()
        ->and(Route::has('products.categories.update'))->toBeTrue()
        ->and(Route::has('products.categories.destroy'))->toBeTrue();
});

it('CRUD raíz e hija; slug auto; update no toca slug; soft delete hoja; auditoría', function () {
    $company = Company::factory()->create();
    $user = actingCategoriesUser($company);

    $this->post(route('products.categories.store'), [
        'name' => 'Electrónica',
    ])->assertRedirect();

    $root = Category::query()->where('name', 'Electrónica')->firstOrFail();

    expect($root->company_id)->toBe($company->id)
        ->and($root->slug)->toBe('electronica')
        ->and($root->parent_id)->toBeNull()
        ->and($root->status)->toBe(CategoryStatus::Inactive)
        ->and($root->created_by)->toBe($user->id);

    $this->post(route('products.categories.store'), [
        'name' => 'Móviles',
        'parent_id' => $root->id,
        'status' => CategoryStatus::Active->value,
    ])->assertRedirect();

    $child = Category::query()->where('name', 'Móviles')->firstOrFail();
    expect($child->parent_id)->toBe($root->id)
        ->and($child->status)->toBe(CategoryStatus::Active);

    $originalSlug = $root->slug;

    $this->put(route('products.categories.update', $root), [
        'name' => 'Electrónica premium',
        'status' => CategoryStatus::Active->value,
        'parent_id' => null,
        'slug' => 'hacked',
    ])->assertRedirect()
        ->assertSessionHasErrors(['slug']);

    $this->put(route('products.categories.update', $root), [
        'name' => 'Electrónica premium',
        'status' => CategoryStatus::Active->value,
        'parent_id' => null,
    ])->assertRedirect();

    $root->refresh();
    expect($root->name)->toBe('Electrónica premium')
        ->and($root->slug)->toBe($originalSlug)
        ->and($root->status)->toBe(CategoryStatus::Active)
        ->and($root->updated_by)->toBe($user->id);

    $this->get(route('products.categories.table'))
        ->assertOk()
        ->assertJsonPath('total', 2);

    $this->delete(route('products.categories.destroy', $child))
        ->assertRedirect(route('products.categories.index'));

    expect(Category::query()->whereKey($child->id)->exists())->toBeFalse()
        ->and(Category::withTrashed()->whereKey($child->id)->exists())->toBeTrue();
});

it('rechaza ciclo al asignar descendiente como padre', function () {
    $company = Company::factory()->create();
    actingCategoriesUser($company);

    $root = Category::factory()->create(['company_id' => $company->id]);
    $child = Category::factory()->childOf($root)->create();
    $grand = Category::factory()->childOf($child)->create();

    $this->from(route('products.categories.edit', $root))
        ->put(route('products.categories.update', $root), [
            'name' => $root->name,
            'status' => $root->status->value,
            'parent_id' => $grand->id,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['parent_id']);

    expect($root->fresh()->parent_id)->toBeNull();
});

it('bloquea delete con hijos y permite delete hoja', function () {
    $company = Company::factory()->create();
    actingCategoriesUser($company);

    $root = Category::factory()->create(['company_id' => $company->id]);
    $child = Category::factory()->childOf($root)->create();

    $this->from(route('products.categories.index'))
        ->delete(route('products.categories.destroy', $root))
        ->assertRedirect()
        ->assertSessionHasErrors(['category']);

    expect(Category::query()->whereKey($root->id)->exists())->toBeTrue()
        ->and(Category::query()->whereKey($child->id)->exists())->toBeTrue();

    $this->delete(route('products.categories.destroy', $child))->assertRedirect();
    $this->delete(route('products.categories.destroy', $root))->assertRedirect();

    expect(Category::query()->whereKey($root->id)->exists())->toBeFalse();
});

it('Index y Create renderizan páginas Inertia', function () {
    actingCategoriesUser(Company::factory()->create());

    $this->get(route('products.categories.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Catalog/Categories/Index'));

    $this->get(route('products.categories.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Catalog/Categories/Create')
            ->has('parent_options'));
});

it('aísla categorías entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $catA = Category::factory()->create(['company_id' => $companyA->id, 'name' => 'Cat A']);
    $catB = Category::factory()->create(['company_id' => $companyB->id, 'name' => 'Cat B']);

    actingCategoriesUser($companyA);

    expect(Category::query()->pluck('id')->all())
        ->toContain($catA->id)
        ->not->toContain($catB->id);

    $this->get(route('products.categories.table'))
        ->assertOk()
        ->assertJsonPath('total', 1);

    $this->get(route('products.categories.edit', $catB))->assertNotFound();
    $this->put(route('products.categories.update', $catB), [
        'name' => 'Hacked',
        'status' => 'active',
        'parent_id' => null,
    ])->assertNotFound();
    $this->delete(route('products.categories.destroy', $catB))->assertNotFound();
});

it('responde 403 sin permiso categories', function () {
    $company = Company::factory()->create();
    $category = Category::factory()->create(['company_id' => $company->id]);

    actingCategoriesUser($company, ['products.access']);

    $this->get(route('products.categories.index'))->assertForbidden();
    $this->post(route('products.categories.store'), ['name' => 'X'])->assertForbidden();
    $this->get(route('products.categories.edit', $category))->assertForbidden();
    $this->delete(route('products.categories.destroy', $category))->assertForbidden();
});
