<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Admin\Support\MenuCatalogCache;
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
        'label' => ['es' => 'Configuración', 'en' => 'Settings'],
        'color' => '#0f766e',
        'icon' => 'settings',
        'level' => ModuleLevel::AdminOnly->value,
        'status' => ModuleStatus::Active->value,
    ]);

    foreach ([
        ['Modules', 'modules', ['es' => 'Módulos']],
        ['Roles', 'roles', ['es' => 'Roles']],
        ['Permissions', 'permissions', ['es' => 'Permisos']],
        ['Countries', 'countries', ['es' => 'Países']],
        ['Currencies', 'currencies', ['es' => 'Monedas']],
        ['Banks', 'banks', ['es' => 'Bancos']],
        ['Units', 'units', ['es' => 'Unidades']],
        ['VAT types', 'vat-types', ['es' => 'Tipos de IVA']],
        ['Accounting account types', 'accounting-account-types', ['es' => 'Grupos contables']],
        ['Module authorizations', 'company-module-authorizations', ['es' => 'Autorización de módulos']],
    ] as [$name, $slug, $label]) {
        app(CreateFunctionality::class)->handle([
            'module_id' => $this->configuracion->id,
            'name' => $name,
            'slug' => $slug,
            'label' => $label,
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    $this->empresas = app(CreateModule::class)->handle([
        'name' => 'Empresas',
        'label' => ['es' => 'Empresas'],
        'color' => '#0369a1',
        'icon' => 'briefcase',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->empresas->id,
        'name' => 'Companies',
        'slug' => 'companies',
        'label' => ['es' => 'Mis empresas'],
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

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->usuarios->id,
        'name' => 'Company users',
        'slug' => 'company-users',
        'label' => ['es' => 'Usuarios empresa'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $this->cuenta = app(CreateModule::class)->handle([
        'name' => 'Cuenta',
        'label' => ['es' => 'Cuenta'],
        'color' => '#7c3aed',
        'icon' => 'user',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->cuenta->id,
        'name' => 'Company modules',
        'slug' => 'company-modules',
        'label' => ['es' => 'Mis módulos'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    foreach (['dashboard', 'empresas', 'usuarios', 'cuenta'] as $slug) {
        enableModuleForCompany($this->company, $slug);
    }

    MenuCatalogCache::forget();
});

it('SA ve Configuración como Module con catálogo', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($sa);
    $config = collect($menu)->firstWhere('slug', 'configuracion');
    $slugs = collect($config['functionalities'])->pluck('slug')->all();

    expect(collect($menu)->pluck('slug')->all())->toContain('configuracion')
        ->and($slugs)->toContain('modules', 'roles', 'permissions', 'countries', 'currencies', 'banks', 'units', 'vat-types', 'accounting-account-types')
        ->and($slugs)->not->toContain('companies', 'company-modules', 'functionalities');
});

it('SA ve Mis empresas bajo Module empresas', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($sa);
    $empresas = collect($menu)->firstWhere('slug', 'empresas');
    $companies = collect($empresas['functionalities'] ?? [])->firstWhere('slug', 'companies');
    $configSlugs = collect($menu)->firstWhere('slug', 'configuracion')['functionalities'];

    expect($empresas)->not->toBeNull()
        ->and($companies['label'])->toBe(__('mis_empresas'))
        ->and($companies['href'])->toBe(route('partners.companies.index', absolute: false))
        ->and(collect($configSlugs)->pluck('slug')->all())->not->toContain('companies');
});

it('no-SA sin comodín configuracion no ve Configuración', function () {
    Permission::findOrCreate('company-roles.viewAny');
    Permission::findOrCreate('usuarios.access');

    $user = User::factory()->create();
    $user->givePermissionTo(['company-roles.viewAny', 'usuarios.access']);
    withCurrentCompany($this->company);

    $slugs = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();

    expect($slugs)->not->toContain('configuracion')
        ->and($slugs)->toContain('usuarios');
});

it('Usuarios empresa apunta a Members de la company de sesión', function () {
    $user = User::factory()->create();
    $user->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo([
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-users.viewAny'),
    ]);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($user);
    setPermissionsTeamId(null);

    $usuarios = collect($menu)->firstWhere('slug', 'usuarios');
    $item = collect($usuarios['functionalities'] ?? [])->firstWhere('slug', 'company-users');

    expect($usuarios)->not->toBeNull()
        ->and($item['label'])->toBe(__('usuarios_empresa'))
        ->and($item['href'])->toBe(route('partners.companies.members.index', $this->company, absolute: false));
});

it('no-SA con usuarios.access y company-users.viewAny abre Members', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo([
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-users.viewAny'),
    ]);
    setPermissionsTeamId(null);
    actingWithCompany($user, $this->company);

    $this->get(route('partners.companies.members.index', $this->company))->assertOk();
});

it('SA abre Members de su company vinculada', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $this->company);

    $this->get(route('partners.companies.members.index', $this->company))->assertOk();
});

it('Mis módulos bajo Module cuenta', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::findOrCreate('cuenta.access'),
        Permission::findOrCreate('company-modules.viewAny'),
    ]);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($user);
    $cuenta = collect($menu)->firstWhere('slug', 'cuenta');
    $item = collect($cuenta['functionalities'] ?? [])->firstWhere('slug', 'company-modules');

    expect($cuenta)->not->toBeNull()
        ->and($item['label'])->toBe(__('mis_modulos'))
        ->and($item['href'])->toBe(route('admin.company-modules.index', absolute: false));
});
