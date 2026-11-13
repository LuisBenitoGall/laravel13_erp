<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    MenuCatalogCache::forget();

    $this->company = Company::factory()->create();
    withCurrentCompany($this->company);
});

it('acepta sort en mass-assignment de Module y Functionality', function () {
    $module = Module::query()->create([
        'name' => 'Sortable',
        'slug' => 'sortable-mod',
        'label' => ['es' => 'Sortable'],
        'color' => '#000000',
        'icon' => 'box',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
        'sort' => 42,
    ]);

    $functionality = Functionality::query()->create([
        'module_id' => $module->id,
        'name' => 'Sortable func',
        'slug' => 'sortable-func',
        'label' => ['es' => 'Func'],
        'status' => FunctionalityStatus::Active,
        'sort' => 7,
    ]);

    expect($module->fresh()->sort)->toBe(42)
        ->and($functionality->fresh()->sort)->toBe(7);
});

it('ordena raíces del menú por sort ASC, igualdad por slug, NULL al final', function () {
    $zebra = app(CreateModule::class)->handle([
        'name' => 'Zebra',
        'label' => ['es' => 'Zebra'],
        'color' => '#111111',
        'icon' => 'z',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
        'sort' => 20,
    ]);

    $alpha = app(CreateModule::class)->handle([
        'name' => 'Alpha',
        'label' => ['es' => 'Alpha'],
        'color' => '#222222',
        'icon' => 'a',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
        'sort' => 10,
    ]);

    $beta = app(CreateModule::class)->handle([
        'name' => 'Beta',
        'label' => ['es' => 'Beta'],
        'color' => '#333333',
        'icon' => 'b',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
        'sort' => 10,
    ]);

    $nullish = app(CreateModule::class)->handle([
        'name' => 'Nullish',
        'label' => ['es' => 'Nullish'],
        'color' => '#444444',
        'icon' => 'n',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);
    expect($nullish->fresh()->sort)->toBeNull();

    foreach ([$zebra, $alpha, $beta, $nullish] as $module) {
        enableModuleForCompany($this->company, $module->slug);
    }

    MenuCatalogCache::forget();

    $user = User::factory()->create();
    foreach ([$zebra, $alpha, $beta, $nullish] as $module) {
        Permission::findOrCreate($module->slug.'.access');
        $user->givePermissionTo($module->slug.'.access');
    }

    $catalogSlugs = MenuCatalogCache::modules()->pluck('slug')->all();
    expect($catalogSlugs)->toBe(['alpha', 'beta', 'zebra', 'nullish']);

    $menuSlugs = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();
    expect($menuSlugs)->toBe(['alpha', 'beta', 'zebra', 'nullish']);
});

it('ordena Functionalities por sort ASC, igualdad por slug, NULL al final', function () {
    $module = app(CreateModule::class)->handle([
        'name' => 'Catalog',
        'label' => ['es' => 'Catálogo'],
        'color' => '#0f766e',
        'icon' => 'box',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
        'sort' => 1,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $module->id,
        'name' => 'Zulu',
        'slug' => 'catalog-zulu',
        'label' => ['es' => 'Zulu'],
        'status' => FunctionalityStatus::Active->value,
        'sort' => 20,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $module->id,
        'name' => 'Alpha',
        'slug' => 'catalog-alpha',
        'label' => ['es' => 'Alpha'],
        'status' => FunctionalityStatus::Active->value,
        'sort' => 10,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $module->id,
        'name' => 'Bravo',
        'slug' => 'catalog-bravo',
        'label' => ['es' => 'Bravo'],
        'status' => FunctionalityStatus::Active->value,
        'sort' => 10,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $module->id,
        'name' => 'Nullish',
        'slug' => 'catalog-nullish',
        'label' => ['es' => 'Nullish'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'catalog');
    MenuCatalogCache::forget();

    $user = User::factory()->create();
    Permission::findOrCreate('catalog.access');
    foreach (['catalog-zulu', 'catalog-alpha', 'catalog-bravo', 'catalog-nullish'] as $slug) {
        Permission::findOrCreate("{$slug}.view");
    }
    $user->givePermissionTo([
        'catalog.access',
        'catalog-zulu.view',
        'catalog-alpha.view',
        'catalog-bravo.view',
        'catalog-nullish.view',
    ]);

    $expected = ['catalog-alpha', 'catalog-bravo', 'catalog-zulu', 'catalog-nullish'];

    $fromCache = MenuCatalogCache::modules()
        ->firstWhere('slug', 'catalog')['functionalities'] ?? [];
    expect(collect($fromCache)->pluck('slug')->all())->toBe($expected);

    $menu = app(MenuBuilder::class)->forUser($user);
    $tenant = collect($menu)->firstWhere('slug', 'catalog');

    expect($tenant)->not->toBeNull()
        ->and(collect($tenant['functionalities'])->pluck('slug')->all())->toBe($expected);
});
