<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\FamilyStatus;
use App\Domains\Catalog\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/CatalogTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('expone rutas products.families.*', function () {
    expect(Route::has('products.families.index'))->toBeTrue()
        ->and(Route::has('products.families.table'))->toBeTrue()
        ->and(Route::has('products.families.create'))->toBeTrue()
        ->and(Route::has('products.families.store'))->toBeTrue()
        ->and(Route::has('products.families.edit'))->toBeTrue()
        ->and(Route::has('products.families.update'))->toBeTrue()
        ->and(Route::has('products.families.destroy'))->toBeTrue();
});

it('CRUD de familias: slug auto; default Inactive; update no toca slug; soft delete; auditoría', function () {
    $company = Company::factory()->create();
    $user = actingFamiliesUser($company);

    $this->post(route('products.families.store'), [
        'name' => 'Electrónica',
    ])->assertRedirect();

    $family = Family::query()->where('name', 'Electrónica')->firstOrFail();

    expect($family->company_id)->toBe($company->id)
        ->and($family->slug)->toBe('electronica')
        ->and($family->status)->toBe(FamilyStatus::Inactive)
        ->and($family->created_by)->toBe($user->id)
        ->and($family->updated_by)->toBe($user->id);

    $originalSlug = $family->slug;

    $this->put(route('products.families.update', $family), [
        'name' => 'Electrónica premium',
        'status' => FamilyStatus::Active->value,
        'slug' => 'hacked-slug',
    ])->assertRedirect()
        ->assertSessionHasErrors(['slug']);

    expect($family->fresh()->slug)->toBe($originalSlug)
        ->and($family->fresh()->status)->toBe(FamilyStatus::Inactive)
        ->and($family->fresh()->name)->toBe('Electrónica');

    $this->put(route('products.families.update', $family), [
        'name' => 'Electrónica premium',
        'status' => FamilyStatus::Active->value,
    ])->assertRedirect();

    $family->refresh();

    expect($family->name)->toBe('Electrónica premium')
        ->and($family->slug)->toBe($originalSlug)
        ->and($family->status)->toBe(FamilyStatus::Active)
        ->and($family->updated_by)->toBe($user->id);

    $this->get(route('products.families.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Electrónica premium')
        ->assertJsonPath('data.0.slug', $originalSlug);

    $this->get(route('products.families.edit', $family))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Catalog/Families/Edit')
            ->where('family.slug', $originalSlug)
            ->where('family.status', 'active'));

    $this->delete(route('products.families.destroy', $family))
        ->assertRedirect(route('products.families.index'));

    expect(Family::query()->whereKey($family->id)->exists())->toBeFalse()
        ->and(Family::withTrashed()->whereKey($family->id)->exists())->toBeTrue();
});

it('Index y Create renderizan páginas Inertia de Families', function () {
    actingFamiliesUser(Company::factory()->create());

    $this->get(route('products.families.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Catalog/Families/Index'));

    $this->get(route('products.families.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Catalog/Families/Create'));
});

it('valida name en store; slug prohibited', function () {
    actingFamiliesUser(Company::factory()->create());

    $this->from(route('products.families.create'))
        ->post(route('products.families.store'), [
            'name' => '',
            'slug' => 'manual',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['name', 'slug']);
});

it('genera slug único ante colisión en la misma company', function () {
    $company = Company::factory()->create();
    actingFamiliesUser($company);

    Family::factory()->create([
        'company_id' => $company->id,
        'name' => 'Hogar',
        'slug' => 'hogar',
    ]);

    $this->post(route('products.families.store'), [
        'name' => 'Hogar',
        'status' => FamilyStatus::Active->value,
    ])->assertRedirect();

    $created = Family::query()->where('name', 'Hogar')->where('slug', '!=', 'hogar')->first();

    expect($created)->not->toBeNull()
        ->and($created->slug)->toStartWith('hogar-')
        ->and($created->status)->toBe(FamilyStatus::Active);
});

it('reutiliza el slug base tras soft-delete de la familia homónima', function () {
    $company = Company::factory()->create();
    actingFamiliesUser($company);

    $this->post(route('products.families.store'), [
        'name' => 'Electrónica',
    ])->assertRedirect();

    $first = Family::query()->where('name', 'Electrónica')->firstOrFail();
    expect($first->slug)->toBe('electronica');

    $this->delete(route('products.families.destroy', $first))
        ->assertRedirect(route('products.families.index'));

    expect(Family::query()->whereKey($first->id)->exists())->toBeFalse()
        ->and(Family::withTrashed()->whereKey($first->id)->exists())->toBeTrue();

    $this->post(route('products.families.store'), [
        'name' => 'Electrónica',
    ])->assertRedirect();

    $second = Family::query()->where('name', 'Electrónica')->firstOrFail();

    expect($second->id)->not->toBe($first->id)
        ->and($second->slug)->toBe('electronica')
        ->and($second->slug)->not->toStartWith('electronica-');
});

it('aísla familias entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $familyA = Family::factory()->create(['company_id' => $companyA->id, 'name' => 'Fam A']);
    $familyB = Family::factory()->create(['company_id' => $companyB->id, 'name' => 'Fam B']);

    actingFamiliesUser($companyA);

    expect(Family::query()->pluck('id')->all())
        ->toContain($familyA->id)
        ->not->toContain($familyB->id);

    $this->get(route('products.families.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Fam A');

    $this->get(route('products.families.edit', $familyB))->assertNotFound();
    $this->put(route('products.families.update', $familyB), [
        'name' => 'Hacked',
        'status' => 'active',
    ])->assertNotFound();
    $this->delete(route('products.families.destroy', $familyB))->assertNotFound();

    expect($familyB->fresh()->name)->toBe('Fam B');
});

it('responde 403 sin permiso de families', function () {
    $company = Company::factory()->create();
    $family = Family::factory()->create(['company_id' => $company->id]);

    actingFamiliesUser($company, ['products.access']);

    $this->get(route('products.families.index'))->assertForbidden();
    $this->get(route('products.families.table'))->assertForbidden();
    $this->get(route('products.families.create'))->assertForbidden();
    $this->post(route('products.families.store'), [
        'name' => 'X',
    ])->assertForbidden();
    $this->get(route('products.families.edit', $family))->assertForbidden();
    $this->put(route('products.families.update', $family), [
        'name' => 'X',
        'status' => 'active',
    ])->assertForbidden();
    $this->delete(route('products.families.destroy', $family))->assertForbidden();
});
