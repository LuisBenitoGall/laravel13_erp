<?php

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Role;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    enableModuleForCompany($this->company, 'configuracion');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

it('Super Admin abre Index de países', function () {
    $this->get(route('admin.countries.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Countries/Index'));
});

it('Index países sin columna Estado y drill Map distinto de Pencil', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Countries/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain('icon: Map')
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->not->toContain('ListTree');
});

it('sin countries.* las rutas de países responden 403', function () {
    $country = Country::factory()->create();

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.countries.index'))->assertForbidden();
    $this->getJson(route('admin.countries.table'))->assertForbidden();
    $this->post(route('admin.countries.store'), ['name' => 'Nuevo'])->assertForbidden();
    $this->put(route('admin.countries.update', $country), ['name' => 'X', 'status' => 'active'])->assertForbidden();
    $this->patch(route('admin.countries.status', $country))->assertForbidden();
    $this->delete(route('admin.countries.destroy', $country))->assertForbidden();

    expect(Country::query()->count())->toBe(1);
});

it('usuario con countries.view abre el Index', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $user->givePermissionTo(['configuracion.access', 'countries.view']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.countries.index'))->assertOk();
});

it('crea, actualiza y valida unicidad de nombre de país', function () {
    $this->post(route('admin.countries.store'), [
        'name' => 'Narnia',
        'code' => '999',
        'alfa2' => 'NA',
        'alfa3' => 'NAR',
    ])->assertRedirect();

    $country = Country::query()->where('name', 'Narnia')->firstOrFail();

    expect($country->slug)->toBe('narnia')
        ->and($country->status)->toBe(GeographyStatus::Active)
        ->and($country->alfa2)->toBe('NA');

    $this->post(route('admin.countries.store'), ['name' => 'narnia'])
        ->assertSessionHasErrors('name');

    $other = Country::factory()->create(['name' => 'Otro']);

    $this->put(route('admin.countries.update', $other), ['name' => 'NARNIA', 'status' => 'active'])
        ->assertSessionHasErrors('name');

    $this->put(route('admin.countries.update', $country), [
        'name' => 'Narnia Libre',
        'code' => '998',
        'alfa2' => 'NL',
        'alfa3' => 'NLI',
        'status' => 'inactive',
    ])->assertRedirect();

    $country->refresh();

    expect($country->name)->toBe('Narnia Libre')
        ->and($country->slug)->toBe('narnia')
        ->and($country->status)->toBe(GeographyStatus::Inactive);

    $this->post(route('admin.countries.store'), ['name' => 'Mal', 'alfa2' => 'ABC'])
        ->assertSessionHasErrors('alfa2');
});

it('toggle status alterna Active/Inactive y acepta status explícito', function () {
    $country = Country::factory()->create();

    $this->patchJson(route('admin.countries.status', $country))
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    $this->patchJson(route('admin.countries.status', $country))
        ->assertOk()
        ->assertJson(['status' => 'active']);

    $this->patchJson(route('admin.countries.status', $country), ['status' => 'inactive'])
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    expect($country->refresh()->status)->toBe(GeographyStatus::Inactive);
});

it('borra país sin hijos (soft delete) y bloquea si tiene provincias', function () {
    $blocked = Country::factory()->create();
    $child = Province::factory()->create(['country_id' => $blocked->id]);

    $this->delete(route('admin.countries.destroy', $blocked))
        ->assertSessionHasErrors('country');

    expect(Country::query()->whereKey($blocked->id)->exists())->toBeTrue();

    $child->delete();

    $this->delete(route('admin.countries.destroy', $blocked))
        ->assertRedirect(route('admin.countries.index'));

    expect(Country::query()->whereKey($blocked->id)->exists())->toBeFalse()
        ->and(Country::withTrashed()->whereKey($blocked->id)->exists())->toBeTrue();
});

it('table de países filtra por search y expone provinces_count', function () {
    $country = Country::factory()->create(['name' => 'Zzland']);
    Province::factory()->count(2)->create(['country_id' => $country->id]);
    Country::factory()->create(['name' => 'Otra']);

    $rows = collect($this->getJson(route('admin.countries.table', [
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Zzl']],
    ]))->assertOk()->json('data'));

    expect($rows)->toHaveCount(1)
        ->and($rows->first()['provinces_count'])->toBe(2);
});
