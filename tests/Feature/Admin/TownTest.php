<?php

use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Town;
use App\Domains\Partners\Models\Company;
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

it('Index poblaciones sin columna Estado', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Towns/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain('icon: Pencil');
});

it('sin countries.* las rutas de poblaciones responden 403', function () {
    $province = Province::factory()->create();
    $town = Town::factory()->create(['province_id' => $province->id]);

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.provinces.towns.index', $province))->assertForbidden();
    $this->getJson(route('admin.provinces.towns.table', $province))->assertForbidden();
    $this->getJson(route('admin.provinces.towns.options', $province))->assertForbidden();
    $this->post(route('admin.provinces.towns.store', $province), ['name' => 'Nueva'])->assertForbidden();
    $this->put(route('admin.towns.update', $town), ['name' => 'X', 'status' => 'active'])->assertForbidden();
    $this->patch(route('admin.towns.status', $town))->assertForbidden();
    $this->delete(route('admin.towns.destroy', $town))->assertForbidden();

    expect(Town::query()->count())->toBe(1);
});

it('poblaciones: listado solo de la provincia padre y CRUD anidado', function () {
    $girona = Province::factory()->create();
    $lleida = Province::factory()->create();
    Town::factory()->create(['province_id' => $girona->id, 'name' => 'Figueres']);
    Town::factory()->create(['province_id' => $lleida->id, 'name' => 'Balaguer']);

    $this->get(route('admin.provinces.towns.index', $girona))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Towns/Index')
            ->where('province.id', $girona->id)
            ->where('country.id', $girona->country_id));

    $names = collect($this->getJson(route('admin.provinces.towns.table', $girona))->assertOk()->json('data'))
        ->pluck('name');

    expect($names->all())->toBe(['Figueres']);

    $this->post(route('admin.provinces.towns.store', $girona), ['name' => 'Olot'])->assertRedirect();

    $olot = Town::query()->where('name', 'Olot')->firstOrFail();

    expect($olot->province_id)->toBe($girona->id)
        ->and($olot->slug)->toBe('olot');

    $this->post(route('admin.provinces.towns.store', $girona), ['name' => 'OLOT'])
        ->assertSessionHasErrors('name');

    $this->put(route('admin.towns.update', $olot), ['name' => 'Olot Vell', 'status' => 'inactive'])
        ->assertRedirect();

    expect($olot->refresh()->name)->toBe('Olot Vell')
        ->and($olot->status)->toBe(GeographyStatus::Inactive);

    $this->patchJson(route('admin.towns.status', $olot))
        ->assertOk()
        ->assertJson(['status' => 'active']);

    $this->delete(route('admin.towns.destroy', $olot))
        ->assertRedirect(route('admin.provinces.towns.index', $girona));

    expect(Town::query()->whereKey($olot->id)->exists())->toBeFalse()
        ->and(Town::withTrashed()->whereKey($olot->id)->exists())->toBeTrue();
});
