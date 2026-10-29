<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
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

    foreach ([
        ['Modules', 'modules', ['es' => 'Módulos']],
        ['Roles', 'roles', ['es' => 'Roles']],
        ['Permissions', 'permissions', ['es' => 'Permisos']],
        ['Companies', 'companies', ['es' => 'Mis empresas']],
        ['Company modules', 'company-modules', ['es' => 'Módulos de empresa']],
    ] as [$name, $slug, $label]) {
        app(CreateFunctionality::class)->handle([
            'module_id' => $this->admin->id,
            'name' => $name,
            'slug' => $slug,
            'label' => $label,
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    $this->workspace = Module::factory()->create([
        'slug' => 'workspace',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->workspace->id,
        'name' => 'Company roles',
        'slug' => 'company-roles',
        'label' => ['es' => 'Roles de empresa'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->workspace->id,
        'name' => 'Company users',
        'slug' => 'company-users',
        'label' => ['es' => 'Usuarios empresa'],
        'status' => FunctionalityStatus::Active->value,
    ]);
});

it('SA ve Configuración con Módulos Roles Permisos y Permisos apunta a roles', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($sa);
    $config = collect($menu)->firstWhere('slug', 'configuracion');
    $slugs = collect($config['functionalities'])->pluck('slug')->all();
    $permissions = collect($config['functionalities'])->firstWhere('slug', 'permissions');

    expect(collect($menu)->pluck('slug')->all())->toContain('configuracion')
        ->and($slugs)->toContain('modules', 'roles', 'permissions')
        ->and($slugs)->not->toContain('companies', 'company-modules')
        ->and($permissions['href'])->toBe(route('admin.roles.index', absolute: false))
        ->and($permissions['label'])->toBe(__('permisos'));
});

it('SA ve Mis empresas bajo pilar Empresas fuera de Configuración', function () {
    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($sa);
    $empresas = collect($menu)->firstWhere('slug', 'empresas');
    $companies = collect($empresas['functionalities'] ?? [])->firstWhere('slug', 'companies');

    $configSlugs = collect($menu)->firstWhere('slug', 'configuracion')['functionalities'];

    expect($empresas)->not->toBeNull()
        ->and($empresas['label'])->toBe(__('empresas'))
        ->and($companies['label'])->toBe(__('mis_empresas'))
        ->and($companies['href'])->toBe(route('partners.companies.index', absolute: false))
        ->and(collect($configSlugs)->pluck('slug')->all())->not->toContain('companies');
});

it('no-SA no ve Configuración ni pilar Empresas operador', function () {
    enableModuleForCompany($this->company, 'workspace');
    Permission::findOrCreate('company-roles.viewAny');

    $user = User::factory()->create();
    $user->givePermissionTo('company-roles.viewAny');
    withCurrentCompany($this->company);

    $slugs = collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all();

    expect($slugs)->not->toContain('configuracion')
        ->and($slugs)->not->toContain('empresas')
        ->and($slugs)->toContain('empresa');
});

it('Usuarios empresa apunta a Members de la company de sesión', function () {
    enableModuleForCompany($this->company, 'workspace');

    $user = User::factory()->create();
    $user->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo(Permission::findOrCreate('company-users.viewAny'));
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($user);
    setPermissionsTeamId(null);

    $usuarios = collect($menu)->firstWhere('slug', 'usuarios');
    $item = collect($usuarios['functionalities'] ?? [])->firstWhere('slug', 'company-users');

    expect($usuarios)->not->toBeNull()
        ->and($usuarios['label'])->toBe(__('usuarios'))
        ->and($item['label'])->toBe(__('usuarios_empresa'))
        ->and($item['href'])->toBe(route('partners.companies.members.index', $this->company, absolute: false));
});

it('no-SA con company-users.viewAny abre Usuarios empresa (Members) sin 403 de módulo', function () {
    enableModuleForCompany($this->company, 'workspace');

    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo(Permission::findOrCreate('company-users.viewAny'));
    setPermissionsTeamId(null);
    actingWithCompany($user, $this->company);

    $this->get(route('partners.companies.members.index', $this->company))->assertOk();
});

it('SA abre Members de su company vinculada', function () {
    enableModuleForCompany($this->company, 'workspace');

    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $this->company);

    $this->get(route('partners.companies.members.index', $this->company))->assertOk();
});
