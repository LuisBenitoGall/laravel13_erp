<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\TagStatus;
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

it('expone rutas products.tags.*', function () {
    expect(Route::has('products.tags.index'))->toBeTrue()
        ->and(Route::has('products.tags.table'))->toBeTrue()
        ->and(Route::has('products.tags.create'))->toBeTrue()
        ->and(Route::has('products.tags.store'))->toBeTrue()
        ->and(Route::has('products.tags.edit'))->toBeTrue()
        ->and(Route::has('products.tags.update'))->toBeTrue()
        ->and(Route::has('products.tags.destroy'))->toBeTrue();
});

it('CRUD de tags: slug auto; default Inactive; update no toca slug; soft delete; auditoría', function () {
    $company = Company::factory()->create();
    $user = actingTagsUser($company);

    $this->post(route('products.tags.store'), [
        'name' => 'Novedad',
    ])->assertRedirect();

    $tag = Tag::query()->where('name', 'Novedad')->firstOrFail();

    expect($tag->company_id)->toBe($company->id)
        ->and($tag->slug)->toBe('novedad')
        ->and($tag->status)->toBe(TagStatus::Inactive)
        ->and($tag->created_by)->toBe($user->id)
        ->and($tag->updated_by)->toBe($user->id);

    $originalSlug = $tag->slug;

    $this->put(route('products.tags.update', $tag), [
        'name' => 'Novedad premium',
        'status' => TagStatus::Active->value,
        'slug' => 'hacked-slug',
    ])->assertRedirect()
        ->assertSessionHasErrors(['slug']);

    expect($tag->fresh()->slug)->toBe($originalSlug)
        ->and($tag->fresh()->status)->toBe(TagStatus::Inactive)
        ->and($tag->fresh()->name)->toBe('Novedad');

    $this->put(route('products.tags.update', $tag), [
        'name' => 'Novedad premium',
        'status' => TagStatus::Active->value,
    ])->assertRedirect();

    $tag->refresh();

    expect($tag->name)->toBe('Novedad premium')
        ->and($tag->slug)->toBe($originalSlug)
        ->and($tag->status)->toBe(TagStatus::Active)
        ->and($tag->updated_by)->toBe($user->id);

    $this->get(route('products.tags.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Novedad premium')
        ->assertJsonPath('data.0.slug', $originalSlug);

    $this->get(route('products.tags.edit', $tag))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Catalog/Tags/Edit')
            ->where('tag.slug', $originalSlug)
            ->where('tag.status', 'active'));

    $this->delete(route('products.tags.destroy', $tag))
        ->assertRedirect(route('products.tags.index'));

    expect(Tag::query()->whereKey($tag->id)->exists())->toBeFalse()
        ->and(Tag::withTrashed()->whereKey($tag->id)->exists())->toBeTrue();
});

it('Index y Create renderizan páginas Inertia de Tags', function () {
    actingTagsUser(Company::factory()->create());

    $this->get(route('products.tags.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Catalog/Tags/Index'));

    $this->get(route('products.tags.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Catalog/Tags/Create'));
});

it('valida name en store; slug prohibited', function () {
    actingTagsUser(Company::factory()->create());

    $this->from(route('products.tags.create'))
        ->post(route('products.tags.store'), [
            'name' => '',
            'slug' => 'manual',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['name', 'slug']);
});

it('reutiliza el slug base tras soft-delete', function () {
    $company = Company::factory()->create();
    actingTagsUser($company);

    $this->post(route('products.tags.store'), ['name' => 'Promo'])->assertRedirect();
    $first = Tag::query()->where('name', 'Promo')->firstOrFail();
    expect($first->slug)->toBe('promo');

    $this->delete(route('products.tags.destroy', $first))->assertRedirect();

    $this->post(route('products.tags.store'), ['name' => 'Promo'])->assertRedirect();
    $second = Tag::query()->where('name', 'Promo')->firstOrFail();

    expect($second->id)->not->toBe($first->id)
        ->and($second->slug)->toBe('promo');
});

it('aísla tags entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $tagA = Tag::factory()->create(['company_id' => $companyA->id, 'name' => 'Tag A']);
    $tagB = Tag::factory()->create(['company_id' => $companyB->id, 'name' => 'Tag B']);

    actingTagsUser($companyA);

    expect(Tag::query()->pluck('id')->all())
        ->toContain($tagA->id)
        ->not->toContain($tagB->id);

    $this->get(route('products.tags.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Tag A');

    $this->get(route('products.tags.edit', $tagB))->assertNotFound();
    $this->put(route('products.tags.update', $tagB), [
        'name' => 'Hacked',
        'status' => 'active',
    ])->assertNotFound();
    $this->delete(route('products.tags.destroy', $tagB))->assertNotFound();
});

it('responde 403 sin permiso de tags', function () {
    $company = Company::factory()->create();
    $tag = Tag::factory()->create(['company_id' => $company->id]);

    actingTagsUser($company, ['products.access']);

    $this->get(route('products.tags.index'))->assertForbidden();
    $this->get(route('products.tags.table'))->assertForbidden();
    $this->get(route('products.tags.create'))->assertForbidden();
    $this->post(route('products.tags.store'), ['name' => 'X'])->assertForbidden();
    $this->get(route('products.tags.edit', $tag))->assertForbidden();
    $this->put(route('products.tags.update', $tag), [
        'name' => 'X',
        'status' => 'active',
    ])->assertForbidden();
    $this->delete(route('products.tags.destroy', $tag))->assertForbidden();
});
