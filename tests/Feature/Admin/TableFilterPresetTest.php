<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\TableFilterPreset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('expone rutas admin.table-filter-presets.*', function () {
    expect(Route::has('admin.table-filter-presets.index'))->toBeTrue()
        ->and(Route::has('admin.table-filter-presets.store'))->toBeTrue()
        ->and(Route::has('admin.table-filter-presets.update'))->toBeTrue()
        ->and(Route::has('admin.table-filter-presets.destroy'))->toBeTrue();
});

it('CRUD JSON de presets: alta, listado por table_key, rename, delete y tope 5', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    $filters = [
        ['field' => 'status', 'type' => 'eq', 'value' => 'active'],
    ];

    $this->postJson(route('admin.table-filter-presets.store'), [
        'table_key' => 'catalog.products',
        'name' => 'Activos',
        'filters' => $filters,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Activos')
        ->assertJsonPath('data.table_key', 'catalog.products');

    $preset = TableFilterPreset::query()->where('name', 'Activos')->firstOrFail();
    expect($preset->company_id)->toBe($company->id)
        ->and($preset->user_id)->toBe($user->id)
        ->and($preset->filters)->toBe($filters);

    for ($i = 2; $i <= 5; $i++) {
        $this->postJson(route('admin.table-filter-presets.store'), [
            'table_key' => 'catalog.products',
            'name' => "Preset {$i}",
            'filters' => $filters,
        ])->assertCreated();
    }

    $this->postJson(route('admin.table-filter-presets.store'), [
        'table_key' => 'catalog.products',
        'name' => 'Sexto',
        'filters' => $filters,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    expect(TableFilterPreset::query()
        ->where('user_id', $user->id)
        ->where('table_key', 'catalog.products')
        ->count())->toBe(5);

    // Otro table_key no cuenta para el tope.
    $this->postJson(route('admin.table-filter-presets.store'), [
        'table_key' => 'catalog.families',
        'name' => 'Otro listado',
        'filters' => $filters,
    ])->assertCreated();

    $this->getJson(route('admin.table-filter-presets.index', [
        'table_key' => 'catalog.products',
    ]))->assertOk()
        ->assertJsonCount(5, 'data');

    $this->patchJson(route('admin.table-filter-presets.update', $preset), [
        'name' => 'Activos v2',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Activos v2');

    $this->deleteJson(route('admin.table-filter-presets.destroy', $preset))
        ->assertNoContent();

    expect(TableFilterPreset::query()->whereKey($preset->id)->exists())->toBeFalse();

    $this->getJson(route('admin.table-filter-presets.index', [
        'table_key' => 'catalog.products',
    ]))->assertOk()
        ->assertJsonCount(4, 'data');
});

it('aísla presets entre usuarios y companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $userA = User::factory()->create(['status' => true]);
    $userB = User::factory()->create(['status' => true]);

    actingWithCompany($userA, $companyA);

    $preset = TableFilterPreset::factory()->create([
        'company_id' => $companyA->id,
        'user_id' => $userA->id,
        'table_key' => 'catalog.products',
        'name' => 'De A',
    ]);

    // Otro usuario misma company.
    actingWithCompany($userB, $companyA);

    $this->getJson(route('admin.table-filter-presets.index', [
        'table_key' => 'catalog.products',
    ]))->assertOk()
        ->assertJsonCount(0, 'data');

    $this->patchJson(route('admin.table-filter-presets.update', $preset), [
        'name' => 'Hack',
    ])->assertForbidden();

    $this->deleteJson(route('admin.table-filter-presets.destroy', $preset))
        ->assertForbidden();

    // Otra company (mismo user A).
    $userA->companies()->syncWithoutDetaching([$companyB->id]);
    actingWithCompany($userA, $companyB);

    $this->getJson(route('admin.table-filter-presets.index', [
        'table_key' => 'catalog.products',
    ]))->assertOk()
        ->assertJsonCount(0, 'data');

    $this->patchJson(route('admin.table-filter-presets.update', $preset), [
        'name' => 'Hack B',
    ])->assertNotFound();
});
