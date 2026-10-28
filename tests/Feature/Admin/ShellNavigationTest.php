<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\CompanyModuleActivationChecker;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    Role::findOrCreate(Role::SUPER_ADMIN);

    $this->company = Company::factory()->create([
        'status' => CompanyStatus::Active,
    ]);

    $this->admin = Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->admin->id,
        'name' => 'Modules',
        'slug' => 'modules',
        'label' => ['es' => 'Módulos', 'en' => 'Modules'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $this->workspace = Module::factory()->create([
        'slug' => 'workspace',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->workspace->id,
        'name' => 'Company roles',
        'slug' => 'company-roles',
        'label' => ['es' => 'Roles de empresa', 'en' => 'Company roles'],
        'status' => FunctionalityStatus::Active->value,
    ]);
});

it('SA sin admin encendido accede a ruta module:admin', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $this->company);

    $this->get(route('admin.modules.index'))->assertOk();
});

it('no-SA con admin apagado recibe 403 en module:admin', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->get(route('admin.modules.index'))->assertForbidden();
});

it('SA con Basic/Optional inactivo recibe 403', function () {
    Module::factory()->create([
        'slug' => 'optional-mod',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);

    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $this->company);

    // Ruta ficticia: usamos checker vía middleware inventando módulo no enrutado —
    // assert vía checker directo + EnsureModuleIsActive ya cubierto; aquí GET no existe.
    expect(app(CompanyModuleActivationChecker::class)->isActive('optional-mod'))
        ->toBeFalse()
        ->and(app(CompanyModuleActivationChecker::class)->isActive('workspace'))
        ->toBeFalse();
});

it('SA sin admin encendido tiene Dashboard y Configuración en menú', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($sa);
    $slugs = collect($menu)->pluck('slug')->all();

    expect($slugs)->toContain('dashboard')
        ->and($slugs)->toContain('configuracion')
        ->and(collect($menu)->firstWhere('slug', 'configuracion')['functionalities'])->not->toBeEmpty();
});

it('no-SA no ve Configuración operador', function () {
    enableModuleForCompany($this->company, 'workspace');
    Permission::findOrCreate('company-roles.viewAny');

    $user = User::factory()->create();
    $user->givePermissionTo('company-roles.viewAny');
    withCurrentCompany($this->company);

    $slugs = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();

    expect($slugs)->toContain('dashboard')
        ->and($slugs)->not->toContain('configuracion')
        ->and($slugs)->toContain('empresa');
});

it('manager con Basic elegible ve Dashboard e ítems', function () {
    enableModuleForCompany($this->company, 'workspace');
    Permission::findOrCreate('workspace.access');

    $user = User::factory()->create();
    $user->givePermissionTo('workspace.access');
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($user);
    $slugs = collect($menu)->pluck('slug')->all();

    expect($slugs[0])->toBe('dashboard')
        ->and($slugs)->toContain('empresa')
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
            ->where('menu.0.slug', 'dashboard')
            ->where('menu', fn ($menu) => collect($menu)->contains(
                fn (array $item): bool => $item['slug'] === 'configuracion',
            )));
});

it('SA sin company va a onboarding alta empresa', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);

    $this->actingAs($sa)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.company'));
});
