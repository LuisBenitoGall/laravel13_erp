<?php

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Enums\GeographyStatus;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\Module;
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

it('modules table sin sorts pone Active antes que Inactive', function () {
    Module::factory()->create([
        'name' => 'Zzz Afsort Active',
        'slug' => 'zzz-afsort-active',
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'name' => 'Aaa Afsort Inactive',
        'slug' => 'aaa-afsort-inactive',
        'status' => ModuleStatus::Inactive,
    ]);

    $rows = collect($this->getJson(route('admin.modules.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Afsort']],
    ]))->assertOk()->json('data'));

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('Zzz Afsort Active')
        ->and($rows[0]['status'])->toBe(ModuleStatus::Active->value)
        ->and($rows[1]['name'])->toBe('Aaa Afsort Inactive')
        ->and($rows[1]['status'])->toBe(ModuleStatus::Inactive->value);
});

it('modules table con sort por name no prefija Active-first', function () {
    Module::factory()->create([
        'name' => 'Zzz Afsort Active',
        'slug' => 'zzz-afsort-active-sort',
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'name' => 'Aaa Afsort Inactive',
        'slug' => 'aaa-afsort-inactive-sort',
        'status' => ModuleStatus::Inactive,
    ]);

    $rows = collect($this->getJson(route('admin.modules.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Afsort']],
        'sort' => [['field' => 'name', 'dir' => 'asc']],
    ]))->assertOk()->json('data'));

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('Aaa Afsort Inactive')
        ->and($rows[1]['name'])->toBe('Zzz Afsort Active');
});

it('countries table sin sorts aplica Active-first y luego name', function () {
    Country::factory()->create([
        'name' => 'Zzz Afsort Active',
        'status' => GeographyStatus::Active,
    ]);
    Country::factory()->inactive()->create([
        'name' => 'Aaa Afsort Inactive',
    ]);
    Country::factory()->create([
        'name' => 'Mmm Afsort Active',
        'status' => GeographyStatus::Active,
    ]);

    $rows = collect($this->getJson(route('admin.countries.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Afsort']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Mmm Afsort Active',
        'Zzz Afsort Active',
        'Aaa Afsort Inactive',
    ]);
});

it('countries table con sort por name no prefija Active-first', function () {
    Country::factory()->create([
        'name' => 'Zzz Afsort Active',
        'status' => GeographyStatus::Active,
    ]);
    Country::factory()->inactive()->create([
        'name' => 'Aaa Afsort Inactive',
    ]);

    $rows = collect($this->getJson(route('admin.countries.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Afsort']],
        'sort' => [['field' => 'name', 'dir' => 'asc']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Aaa Afsort Inactive',
        'Zzz Afsort Active',
    ]);
});

it('provinces table sin sorts aplica Active-first y luego name', function () {
    $country = Country::factory()->create();

    Province::factory()->create([
        'country_id' => $country->id,
        'name' => 'Zzz Afsort Active',
        'status' => GeographyStatus::Active,
    ]);
    Province::factory()->inactive()->create([
        'country_id' => $country->id,
        'name' => 'Aaa Afsort Inactive',
    ]);

    $rows = collect($this->getJson(route('admin.countries.provinces.table', [
        'country' => $country,
        'size' => 200,
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Zzz Afsort Active',
        'Aaa Afsort Inactive',
    ]);
});

it('provinces table con sort por name no prefija Active-first', function () {
    $country = Country::factory()->create();

    Province::factory()->create([
        'country_id' => $country->id,
        'name' => 'Zzz Afsort Active',
        'status' => GeographyStatus::Active,
    ]);
    Province::factory()->inactive()->create([
        'country_id' => $country->id,
        'name' => 'Aaa Afsort Inactive',
    ]);

    $rows = collect($this->getJson(route('admin.countries.provinces.table', [
        'country' => $country,
        'size' => 200,
        'sort' => [['field' => 'name', 'dir' => 'asc']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Aaa Afsort Inactive',
        'Zzz Afsort Active',
    ]);
});

it('towns table sin sorts aplica Active-first y luego name', function () {
    $province = Province::factory()->create();

    Town::factory()->create([
        'province_id' => $province->id,
        'name' => 'Zzz Afsort Active',
        'status' => GeographyStatus::Active,
    ]);
    Town::factory()->inactive()->create([
        'province_id' => $province->id,
        'name' => 'Aaa Afsort Inactive',
    ]);

    $rows = collect($this->getJson(route('admin.provinces.towns.table', [
        'province' => $province,
        'size' => 200,
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Zzz Afsort Active',
        'Aaa Afsort Inactive',
    ]);
});

it('towns table con sort por name no prefija Active-first', function () {
    $province = Province::factory()->create();

    Town::factory()->create([
        'province_id' => $province->id,
        'name' => 'Zzz Afsort Active',
        'status' => GeographyStatus::Active,
    ]);
    Town::factory()->inactive()->create([
        'province_id' => $province->id,
        'name' => 'Aaa Afsort Inactive',
    ]);

    $rows = collect($this->getJson(route('admin.provinces.towns.table', [
        'province' => $province,
        'size' => 200,
        'sort' => [['field' => 'name', 'dir' => 'asc']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Aaa Afsort Inactive',
        'Zzz Afsort Active',
    ]);
});

it('currencies table sin sorts aplica Active-first y luego name', function () {
    Currency::factory()->create([
        'name' => 'Zzz Afsort Active',
        'code' => 'ZAA',
        'status' => CurrencyStatus::Active,
    ]);
    Currency::factory()->inactive()->create([
        'name' => 'Aaa Afsort Inactive',
        'code' => 'AAI',
    ]);
    Currency::factory()->create([
        'name' => 'Mmm Afsort Active',
        'code' => 'MAA',
        'status' => CurrencyStatus::Active,
    ]);

    $rows = collect($this->getJson(route('admin.currencies.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Afsort']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Mmm Afsort Active',
        'Zzz Afsort Active',
        'Aaa Afsort Inactive',
    ]);
});

it('currencies table con sort por name no prefija Active-first', function () {
    Currency::factory()->create([
        'name' => 'Zzz Afsort Active',
        'code' => 'ZAB',
        'status' => CurrencyStatus::Active,
    ]);
    Currency::factory()->inactive()->create([
        'name' => 'Aaa Afsort Inactive',
        'code' => 'AAJ',
    ]);

    $rows = collect($this->getJson(route('admin.currencies.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Afsort']],
        'sort' => [['field' => 'name', 'dir' => 'asc']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Aaa Afsort Inactive',
        'Zzz Afsort Active',
    ]);
});
