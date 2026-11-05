<?php

use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Province;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Town;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\GeographySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

it('geografía es catálogo global sin company_id y visible desde dos companies (R-TEN-05 sustituto)', function () {
    foreach (['countries', 'provinces', 'towns'] as $table) {
        expect(Schema::hasColumn($table, 'company_id'))->toBeFalse();
    }

    $country = Country::factory()->create(['name' => 'Atlantis']);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $names = fn () => collect($this->getJson(route('admin.countries.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis')
        ->and(Country::query()->whereKey($country->id)->exists())->toBeTrue();
});

it('seeder deja la Functionality countries con permisos countries.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "countries.{$ability}")->exists())->toBeTrue();
    }
});

it('options: solo Active, no eliminados, ordenados por nombre con payload {id, name}', function () {
    $country = Country::factory()->create();
    $b = Province::factory()->create(['country_id' => $country->id, 'name' => 'Bbb']);
    $a = Province::factory()->create(['country_id' => $country->id, 'name' => 'Aaa']);
    Province::factory()->inactive()->create(['country_id' => $country->id, 'name' => 'Ccc']);
    Province::factory()->create(['country_id' => $country->id, 'name' => 'Ddd'])->delete();
    Province::factory()->create(['name' => 'Ajena']);

    $this->getJson(route('admin.countries.provinces.options', $country))
        ->assertOk()
        ->assertExactJson([
            ['id' => $a->id, 'name' => 'Aaa'],
            ['id' => $b->id, 'name' => 'Bbb'],
        ]);

    $town2 = Town::factory()->create(['province_id' => $a->id, 'name' => 'Zeta']);
    $town1 = Town::factory()->create(['province_id' => $a->id, 'name' => 'Alfa']);
    Town::factory()->inactive()->create(['province_id' => $a->id, 'name' => 'Inactiva']);
    Town::factory()->create(['province_id' => $a->id, 'name' => 'Borrada'])->delete();
    Town::factory()->create(['province_id' => $b->id, 'name' => 'Ajena']);

    $this->getJson(route('admin.provinces.towns.options', $a))
        ->assertOk()
        ->assertExactJson([
            ['id' => $town1->id, 'name' => 'Alfa'],
            ['id' => $town2->id, 'name' => 'Zeta'],
        ]);
});

it('options acepta usuarios con countries.viewAny', function () {
    $country = Country::factory()->create();
    Province::factory()->create(['country_id' => $country->id]);

    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $user->givePermissionTo(['configuracion.access', 'countries.viewAny']);
    actingWithCompany($user, $this->company);

    $this->getJson(route('admin.countries.provinces.options', $country))
        ->assertOk()
        ->assertJsonCount(1);
});

it('GeographySeeder no falla y preserva ids si hay JSON v1', function () {
    $this->seed(GeographySeeder::class);

    $countriesPath = database_path('data/geography/countries.json');

    if (! is_file($countriesPath)) {
        expect(Country::withTrashed()->count())->toBe(0);

        return;
    }

    /** @var list<array<string, mixed>> $rows */
    $rows = json_decode((string) file_get_contents($countriesPath), true, flags: JSON_THROW_ON_ERROR);
    $first = $rows[0];

    expect(Country::withTrashed()->count())->toBeGreaterThan(0)
        ->and(Country::withTrashed()->whereKey($first['id'])->value('name'))->toBe($first['name']);

    $this->seed(GeographySeeder::class);

    expect(Country::withTrashed()->count())->toBe(count($rows));
});
