<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
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

    $this->dashboard = app(CreateModule::class)->handle([
        'name' => 'Dashboard',
        'label' => ['es' => 'Dashboard', 'en' => 'Dashboard'],
        'color' => '#0f172a',
        'icon' => 'home',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    $this->module = app(CreateModule::class)->handle([
        'name' => 'Catalog',
        'label' => ['es' => 'Catálogo', 'en' => 'Catalog'],
        'color' => '#0f766e',
        'icon' => 'box',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->module->id,
        'name' => 'Items',
        'slug' => 'catalog-items',
        'label' => ['es' => 'Ítems', 'en' => 'Items'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->module->id,
        'name' => 'Categories',
        'slug' => 'catalog-categories',
        'label' => ['es' => 'Categorías', 'en' => 'Categories'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'dashboard');
    enableModuleForCompany($this->company, 'catalog');
    withCurrentCompany($this->company);
    MenuCatalogCache::forget();
});

it('solo view sin comodín no muestra Functionality', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('catalog-items.view');
    $user->givePermissionTo('catalog-items.view');

    $menu = app(MenuBuilder::class)->forUser($user);

    expect(collect($menu)->pluck('slug')->all())->not->toContain('catalog');
});

it('solo comodín sin view no muestra Functionality', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('catalog.access');
    $user->givePermissionTo('catalog.access');

    $menu = app(MenuBuilder::class)->forUser($user);
    $tenant = collect($menu)->firstWhere('slug', 'catalog');

    expect($tenant)->toBeNull();
});

it('comodín y view muestran solo Functionalities con permiso', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('catalog.access');
    Permission::findOrCreate('catalog-items.view');
    $user->givePermissionTo(['catalog.access', 'catalog-items.view']);

    $menu = app(MenuBuilder::class)->forUser($user);
    $tenant = collect($menu)->firstWhere('slug', 'catalog');

    expect($tenant)->not->toBeNull()
        ->and(collect($tenant['functionalities'])->pluck('slug')->all())->toBe(['catalog-items']);
});

it('módulo solo visible si elegible y encendido', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('catalog.access');
    Permission::findOrCreate('catalog-items.view');
    $user->givePermissionTo(['catalog.access', 'catalog-items.view']);

    app(DisableModuleForCompany::class)
        ->handle($this->company, $this->module);

    $slugs = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();

    expect($slugs)->not->toContain('catalog');
});

it('Module hoja dashboard aparece con comodín y capas', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('dashboard.access');
    $user->givePermissionTo('dashboard.access');

    $menu = app(MenuBuilder::class)->forUser($user);
    $dash = collect($menu)->firstWhere('slug', 'dashboard');

    expect($dash)->not->toBeNull()
        ->and($dash['href'] ?? null)->toBe(route('dashboard', absolute: false))
        ->and($dash['functionalities'])->toBe([]);
});

it('raíces del menú corresponden a Modules del catálogo', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('dashboard.access');
    $user->givePermissionTo('dashboard.access');

    $menuSlugs = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug');
    $catalogSlugs = Module::query()->pluck('slug');

    expect($menuSlugs->diff($catalogSlugs)->all())->toBe([]);
});

it('invalidación de cache tras crear Functionality', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('catalog.access');
    Permission::findOrCreate('catalog-items.view');
    $user->givePermissionTo(['catalog.access', 'catalog-items.view']);

    app(MenuBuilder::class)->forUser($user);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->module->id,
        'name' => 'Tags',
        'slug' => 'catalog-tags',
        'label' => ['es' => 'Tags'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    Permission::findOrCreate('catalog-tags.view');
    $user->givePermissionTo('catalog-tags.view');

    $tenant = collect(app(MenuBuilder::class)->forUser($user))->firstWhere('slug', 'catalog');

    expect(collect($tenant['functionalities'])->pluck('slug')->all())
        ->toContain('catalog-tags');
});

it('dos companies filtran menú distinto sobre el mismo catálogo', function () {
    $other = Company::factory()->create();
    enableModuleForCompany($other, 'dashboard');
    // catalog solo en company A

    $user = User::factory()->create();
    Permission::findOrCreate('dashboard.access');
    Permission::findOrCreate('catalog.access');
    Permission::findOrCreate('catalog-items.view');
    $user->givePermissionTo(['dashboard.access', 'catalog.access', 'catalog-items.view']);

    withCurrentCompany($this->company);
    $slugsA = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();

    withCurrentCompany($other);
    $slugsB = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();

    expect($slugsA)->toContain('catalog')
        ->and($slugsB)->not->toContain('catalog')
        ->and($slugsB)->toContain('dashboard');
});
