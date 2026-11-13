<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\CompanyModuleActivationChecker;
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

    Role::findOrCreate(Role::SUPER_ADMIN);

    $this->company = Company::factory()->create([
        'status' => CompanyStatus::Active,
    ]);

    $this->dashboard = app(CreateModule::class)->handle([
        'name' => 'Dashboard',
        'label' => ['es' => 'Dashboard'],
        'color' => '#0f172a',
        'icon' => 'home',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    $this->configuracion = app(CreateModule::class)->handle([
        'name' => 'Configuracion',
        'label' => ['es' => 'Configuración'],
        'color' => '#0f766e',
        'icon' => 'settings',
        'level' => ModuleLevel::AdminOnly->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->configuracion->id,
        'name' => 'Modules',
        'slug' => 'modules',
        'label' => ['es' => 'Módulos'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $this->usuarios = app(CreateModule::class)->handle([
        'name' => 'Usuarios',
        'label' => ['es' => 'Usuarios'],
        'color' => '#0369a1',
        'icon' => 'users',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->usuarios->id,
        'name' => 'Company roles',
        'slug' => 'company-roles',
        'label' => ['es' => 'Roles de empresa'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'dashboard');
    enableModuleForCompany($this->company, 'usuarios');
    MenuCatalogCache::forget();
});

it('SA sin configuracion encendido accede a ruta module:configuracion', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $this->company);

    $this->get(route('admin.modules.index'))->assertOk();
});

it('no-SA con configuracion apagado recibe 403 en module:configuracion', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->get(route('admin.permissions.index'))->assertForbidden();
});

it('SA con Basic/Optional inactivo recibe 403 vía checker', function () {
    app(CreateModule::class)->handle([
        'name' => 'Optional Mod',
        'label' => ['es' => 'Optional'],
        'color' => '#000',
        'icon' => 'box',
        'level' => ModuleLevel::Optional->value,
        'status' => ModuleStatus::Active->value,
    ]);

    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $this->company);

    expect(app(CompanyModuleActivationChecker::class)->isActive('optional-mod'))
        ->toBeFalse()
        ->and(app(CompanyModuleActivationChecker::class)->isActive('usuarios'))
        ->toBeTrue();
});

it('SA sin configuracion encendido tiene Dashboard y Configuración en menú', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($sa);
    $slugs = collect($menu)->pluck('slug')->all();
    $dash = collect($menu)->firstWhere('slug', 'dashboard');

    expect($slugs)->toContain('dashboard')
        ->and($slugs)->toContain('configuracion')
        ->and($dash['href'] ?? null)->toBe(route('dashboard', absolute: false))
        ->and($dash['functionalities'])->toBeEmpty()
        ->and(collect($menu)->firstWhere('slug', 'configuracion')['functionalities'])->not->toBeEmpty();
});

it('no-SA no ve Configuración operador', function () {
    Permission::findOrCreate('company-roles.view');
    Permission::findOrCreate('usuarios.access');

    $user = User::factory()->create();
    $user->givePermissionTo(['company-roles.view', 'usuarios.access']);
    withCurrentCompany($this->company);

    $slugs = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();

    expect($slugs)->not->toContain('configuracion')
        ->and($slugs)->toContain('usuarios');
});

it('manager con Basic elegible ve ítems AND', function () {
    Permission::findOrCreate('usuarios.access');
    Permission::findOrCreate('company-roles.view');
    Permission::findOrCreate('dashboard.access');

    $user = User::factory()->create();
    $user->givePermissionTo(['usuarios.access', 'company-roles.view', 'dashboard.access']);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($user);
    $slugs = collect($menu)->pluck('slug')->all();

    expect($slugs)->toContain('dashboard')
        ->and($slugs)->toContain('usuarios')
        ->and($slugs)->not->toContain('configuracion');
});

it('dashboard y branding se comparten en Inertia', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $this->company);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('branding.logo_url')
            ->has('tenant.logo_url')
            ->where('menu', fn ($menu) => collect($menu)->contains(
                fn (array $item): bool => $item['slug'] === 'dashboard'
                    && ($item['href'] ?? null) === route('dashboard', absolute: false)
                    && ($item['functionalities'] ?? null) === [],
            ))
            ->where('menu', fn ($menu) => collect($menu)->contains(
                fn (array $item): bool => $item['slug'] === 'configuracion',
            )));
});
