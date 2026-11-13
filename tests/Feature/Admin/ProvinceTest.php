<?php

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Town;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

it('Index provincias sin columna Estado y drill MapPin distinto de Pencil', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Provinces/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain('icon: MapPin')
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->not->toContain('ListTree');
});

it('sin countries.* las rutas de provincias responden 403', function () {
    $country = Country::factory()->create();
    $province = Province::factory()->create(['country_id' => $country->id]);

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.countries.provinces.index', $country))->assertForbidden();
    $this->getJson(route('admin.countries.provinces.table', $country))->assertForbidden();
    $this->getJson(route('admin.countries.provinces.options', $country))->assertForbidden();
    $this->post(route('admin.countries.provinces.store', $country), ['name' => 'Nueva'])->assertForbidden();
    $this->put(route('admin.provinces.update', $province), ['name' => 'X', 'status' => 'active'])->assertForbidden();
    $this->patch(route('admin.provinces.status', $province))->assertForbidden();
    $this->delete(route('admin.provinces.destroy', $province))->assertForbidden();

    expect(Province::query()->count())->toBe(1);
});

it('provincias: listado solo del país padre, CRUD anidado y unicidad por país', function () {
    $spain = Country::factory()->create();
    $france = Country::factory()->create();
    Province::factory()->create(['country_id' => $spain->id, 'name' => 'Girona']);
    Province::factory()->create(['country_id' => $france->id, 'name' => 'Paris']);

    $this->get(route('admin.countries.provinces.index', $spain))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Provinces/Index')
            ->where('country.id', $spain->id));

    $names = collect($this->getJson(route('admin.countries.provinces.table', $spain))->assertOk()->json('data'))
        ->pluck('name');

    expect($names->all())->toBe(['Girona']);

    $this->post(route('admin.countries.provinces.store', $spain), ['name' => 'Lleida'])->assertRedirect();

    $lleida = Province::query()->where('name', 'Lleida')->firstOrFail();

    expect($lleida->country_id)->toBe($spain->id)
        ->and($lleida->slug)->toBe('lleida')
        ->and($lleida->status)->toBe(GeographyStatus::Active);

    $this->post(route('admin.countries.provinces.store', $spain), ['name' => 'lleida'])
        ->assertSessionHasErrors('name');

    // Mismo nombre en otro país: permitido.
    $this->post(route('admin.countries.provinces.store', $france), ['name' => 'Lleida'])->assertRedirect();

    $this->put(route('admin.provinces.update', $lleida), ['name' => 'Lérida', 'status' => 'inactive'])
        ->assertRedirect();

    expect($lleida->refresh()->name)->toBe('Lérida')
        ->and($lleida->country_id)->toBe($spain->id)
        ->and($lleida->status)->toBe(GeographyStatus::Inactive);

    $this->patchJson(route('admin.provinces.status', $lleida))
        ->assertOk()
        ->assertJson(['status' => 'active']);

    $this->delete(route('admin.provinces.destroy', $lleida))
        ->assertRedirect(route('admin.countries.provinces.index', $spain));

    expect(Province::query()->whereKey($lleida->id)->exists())->toBeFalse();
});

it('provincia con poblaciones no se puede borrar', function () {
    $province = Province::factory()->create();
    $town = Town::factory()->create(['province_id' => $province->id]);

    $this->delete(route('admin.provinces.destroy', $province))
        ->assertSessionHasErrors('province');

    expect(Province::query()->whereKey($province->id)->exists())->toBeTrue();

    $town->delete();

    $this->delete(route('admin.provinces.destroy', $province))->assertRedirect();

    expect(Province::query()->whereKey($province->id)->exists())->toBeFalse();
});
