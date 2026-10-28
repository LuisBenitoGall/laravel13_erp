<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->company = Company::factory()->create();

    $this->module = app(CreateModule::class)->handle([
        'name' => 'Catalog',
        'slug' => 'catalog',
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

    enableModuleForCompany($this->company, 'catalog');
    withCurrentCompany($this->company);
});

it('muestra módulo con solo viewAny de Functionality sin comodín', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('catalog-items.viewAny');
    $user->givePermissionTo('catalog-items.viewAny');

    $menu = app(MenuBuilder::class)->forUser($user);
    $tenant = collect($menu)->firstWhere('slug', 'catalog');

    expect(collect($menu)->pluck('slug')->all())->toContain('dashboard')
        ->and($tenant)->not->toBeNull()
        ->and(collect($tenant['functionalities'])->pluck('slug')->all())->toBe(['catalog-items']);
});

it('comodín implica todas las Functionalities del módulo', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('catalog.access');
    $user->givePermissionTo('catalog.access');

    $menu = app(MenuBuilder::class)->forUser($user);
    $tenant = collect($menu)->firstWhere('slug', 'catalog');

    expect(collect($tenant['functionalities'])->pluck('slug')->sort()->values()->all())
        ->toBe(['catalog-categories', 'catalog-items']);
});

it('módulo solo visible si elegible y encendido', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('catalog.access');
    $user->givePermissionTo('catalog.access');

    app(DisableModuleForCompany::class)
        ->handle($this->company, $this->module);

    $slugs = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();

    expect($slugs)->toBe(['dashboard']);
});
