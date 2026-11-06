<?php

use App\Domains\Admin\Actions\AssignCompanyRole;
use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\CopyCompanyRoleToCompanies;
use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->company = Company::factory()->create();

    $admin = Module::factory()->create([
        'slug' => 'configuracion',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    app(CreateFunctionality::class)->handle([
        'module_id' => $admin->id,
        'name' => 'Roles',
        'slug' => 'roles',
        'label' => ['es' => 'Roles'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $this->usuarios = app(CreateModule::class)->handle([
        'name' => 'Usuarios',
        'label' => ['es' => 'Usuarios', 'en' => 'Users'],
        'color' => '#0369a1',
        'icon' => 'users',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->usuarios->id,
        'name' => 'Company roles',
        'slug' => 'company-roles',
        'label' => ['es' => 'Roles de empresa', 'en' => 'Company roles'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $this->sales = app(CreateModule::class)->handle([
        'name' => 'Sales',
        'slug' => 'sales',
        'label' => ['es' => 'Ventas', 'en' => 'Sales'],
        'color' => '#0f766e',
        'icon' => 'cart',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->sales->id,
        'name' => 'Budgets',
        'slug' => 'budgets',
        'label' => ['es' => 'Presupuestos'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    Role::findOrCreate(Role::SUPER_ADMIN);
    foreach ([Role::MANAGER, Role::GUEST] as $name) {
        $tpl = Role::findOrCreate($name);
        $tpl->forceFill(['universal' => true])->save();
    }

    app(EnsureCompanyDefaultRoles::class)->handle($this->company);
    app(AuthorizeModuleForCompany::class)->handle($this->company, $this->usuarios);
    app(EnableModuleForCompany::class)->handle($this->company, $this->usuarios);
    app(AuthorizeModuleForCompany::class)->handle($this->company, $this->sales);
    app(EnableModuleForCompany::class)->handle($this->company, $this->sales);
    app(SyncManagerPermissionsForCompany::class)->handle($this->company);
});

function companyManager(): User
{
    $user = User::factory()->create();
    $user->companies()->attach(test()->company->id);
    app(AssignCompanyRole::class)->handle($user, test()->company, Role::MANAGER);
    actingWithCompany($user, test()->company);

    return $user;
}

function companyGuest(): User
{
    $user = User::factory()->create();
    $user->companies()->attach(test()->company->id);
    app(AssignCompanyRole::class)->handle($user, test()->company, Role::GUEST);
    actingWithCompany($user, test()->company);

    return $user;
}

it('permite CRUD de rol custom al manager', function () {
    companyManager();

    $this->post(route('company.roles.store'), [
        'name' => 'vendedor',
        'description' => 'Ventas',
    ])->assertRedirect();

    $role = Role::query()->forCompany($this->company->id)->where('name', 'vendedor')->first();
    expect($role)->not->toBeNull()
        ->and($role->universal)->toBeFalse()
        ->and((int) $role->company_id)->toBe((int) $this->company->id)
        ->and($role->permissions)->toHaveCount(0);

    $this->put(route('company.roles.update', $role), [
        'description' => 'Ventas 2',
        'permissions' => ['budgets.viewAny'],
        'name' => 'hacked',
    ])->assertRedirect();

    $role->refresh();
    expect($role->description)->toBe('Ventas 2')
        ->and($role->name)->toBe('vendedor')
        ->and($role->permissions)->toHaveCount(0);

    $this->patchJson(route('company.roles.permissions.toggle', $role), [
        'permission' => 'budgets.viewAny',
        'enabled' => true,
    ])->assertOk();

    $role->refresh();
    expect($role->permissions->pluck('name')->all())
        ->toContain('budgets.viewAny', 'sales.access');

    $this->delete(route('company.roles.destroy', $role))->assertRedirect(route('company.roles.index'));
    expect(Role::query()->forCompany($this->company->id)->where('name', 'vendedor')->exists())->toBeFalse();
});

it('rechaza nombre reservado y validación de create', function () {
    companyManager();

    $this->post(route('company.roles.store'), [
        'name' => 'manager',
        'permissions' => [],
    ])->assertSessionHasErrors('name');

    $this->post(route('company.roles.store'), [
        'name' => '',
    ])->assertSessionHasErrors('name');
});

it('Index/table con company-roles.view sin ser manager', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo([
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-roles.view'),
    ]);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $this->get(route('company.roles.index'))->assertOk();
    $this->get(route('company.roles.table'))->assertOk();
});

it('Index sin company-roles.view responde 403', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo(Permission::findOrCreate('usuarios.access'));
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $this->get(route('company.roles.index'))->assertForbidden();
    $this->get(route('company.roles.table'))->assertForbidden();
    $this->post(route('company.roles.store'), [
        'name' => 'x',
    ])->assertForbidden();
});

it('guest sin company-roles.view no abre Index', function () {
    companyGuest();

    $this->get(route('company.roles.index'))->assertForbidden();
});

it('no permite borrar manager ni guest locales', function () {
    companyManager();

    $manager = Role::query()->forCompany($this->company->id)->where('name', Role::MANAGER)->firstOrFail();
    $guest = Role::query()->forCompany($this->company->id)->where('name', Role::GUEST)->firstOrFail();

    $this->delete(route('company.roles.destroy', $manager))->assertForbidden();
    $this->delete(route('company.roles.destroy', $guest))->assertForbidden();
});

it('guarda description del manager local y rechaza toggles de permisos', function () {
    companyManager();

    $manager = Role::query()->forCompany($this->company->id)->where('name', Role::MANAGER)->firstOrFail();
    $beforePerms = $manager->permissions->pluck('name')->sort()->values()->all();

    $this->put(route('company.roles.update', $manager), [
        'description' => 'Manager desc',
        'permissions' => ['budgets.viewAny'],
    ])->assertRedirect();

    $manager->refresh();
    expect($manager->description)->toBe('Manager desc')
        ->and($manager->permissions->pluck('name')->sort()->values()->all())->toBe($beforePerms);

    $this->patchJson(route('company.roles.permissions.toggle', $manager), [
        'permission' => 'budgets.viewAny',
        'enabled' => true,
    ])->assertInvalid(['role']);

    $manager->refresh();
    expect($manager->permissions->pluck('name')->sort()->values()->all())->toBe($beforePerms);
});

it('permite editar permisos de guest local vía toggle', function () {
    companyManager();

    $guest = Role::query()->forCompany($this->company->id)->where('name', Role::GUEST)->firstOrFail();

    $this->patchJson(route('company.roles.permissions.toggle', $guest), [
        'permission' => 'budgets.viewAny',
        'enabled' => true,
    ])->assertOk();

    $guest->refresh();
    expect($guest->permissions->pluck('name')->all())
        ->toContain('budgets.viewAny', 'sales.access');
});

it('rechaza borrar custom con usuarios asignados', function () {
    companyManager();

    $this->post(route('company.roles.store'), [
        'name' => 'with_users',
        'permissions' => [],
    ])->assertRedirect();

    $role = Role::query()->forCompany($this->company->id)->where('name', 'with_users')->firstOrFail();

    $other = User::factory()->create();
    $other->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    $other->assignRole($role);

    $this->delete(route('company.roles.destroy', $role))->assertSessionHasErrors('role');
    expect(Role::query()->whereKey($role->id)->exists())->toBeTrue();
});

it('aisla roles entre companies (R-TEN-05)', function () {
    $otherCompany = Company::factory()->create();
    app(EnsureCompanyDefaultRoles::class)->handle($otherCompany);
    app(AuthorizeModuleForCompany::class)->handle($otherCompany, $this->usuarios);
    app(EnableModuleForCompany::class)->handle($otherCompany, $this->usuarios);
    app(SyncManagerPermissionsForCompany::class)->handle($otherCompany);

    setPermissionsTeamId($otherCompany->id);
    $foreign = Role::query()->create([
        'name' => 'foreign_custom',
        'guard_name' => 'web',
        'universal' => false,
    ]);

    companyManager();

    $this->get(route('company.roles.edit', $foreign))->assertNotFound();
    $this->put(route('company.roles.update', $foreign), [
        'description' => 'hack',
    ])->assertNotFound();
    $this->delete(route('company.roles.destroy', $foreign))->assertNotFound();
});

it('Create solo metadatos sin matriz ni universal; POST local y redirect Edit', function () {
    companyManager();

    $this->get(route('company.roles.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyRoles/Create')
            ->missing('matrix')
            ->missing('modules')
            ->missing('permissions'));

    $source = file_get_contents(resource_path('js/Pages/Admin/CompanyRoles/Create.tsx'));
    expect($source)->toContain('RoleMetadataFields')
        ->and($source)->toContain('showUniversal={false}')
        ->and($source)->not->toContain('matriz')
        ->and($source)->not->toContain('rol_universal')
        ->and($source)->not->toContain('CompanyRoleForm');

    $response = $this->post(route('company.roles.store'), [
        'name' => 'metadata_ops',
        'description' => 'Solo metadatos',
        'universal' => true,
        'permissions' => ['budgets.viewAny'],
    ]);

    $role = Role::query()->forCompany($this->company->id)->where('name', 'metadata_ops')->firstOrFail();
    $response->assertRedirect(route('company.roles.edit', $role));

    expect($role->universal)->toBeFalse()
        ->and((int) $role->company_id)->toBe((int) $this->company->id)
        ->and($role->description)->toBe('Solo metadatos')
        ->and($role->permissions)->toHaveCount(0);
});

it('Index cumple contrato ServerTable y CTA Crear', function () {
    companyManager();

    $source = file_get_contents(resource_path('js/Pages/Admin/CompanyRoles/Index.tsx'));

    expect($source)->toContain("href: '/company/roles/create'")
        ->and($source)->toContain("labelKey: 'crear'")
        ->and($source)->toContain('persistKey="company.roles"')
        ->and($source)->toContain('exportable={false}')
        ->and($source)->toContain('rowActions')
        ->and($source)->toContain("headerFilterKind: 'string'")
        ->and($source)->toContain("labelKey: 'editar'")
        ->and($source)->not->toContain("from 'tabulator-tables'")
        ->and($source)->not->toContain("field: 'id'");

    $this->get(route('company.roles.table'))
        ->assertOk()
        ->assertJsonStructure(['data', 'last_page', 'total']);

    $row = collect($this->get(route('company.roles.table'))->json('data'))
        ->firstWhere('name', Role::MANAGER);

    expect($row)->not->toBeNull()
        ->and($row['can_delete'])->toBeFalse()
        ->and($row['can_edit'])->toBeTrue()
        ->and($row)->toHaveKeys(['id', 'name', 'description', 'can_delete', 'can_edit']);

    expect($source)->toContain('can_edit');
});

it('Edit resuelve {companyRole} con sesión aunque CurrentCompany no esté pre-seteado', function () {
    companyManager();

    $this->post(route('company.roles.store'), [
        'name' => 'bind_session_only',
        'description' => 'Regresión binding',
    ])->assertRedirect();

    $role = Role::query()->forCompany($this->company->id)->where('name', 'bind_session_only')->firstOrFail();

    // Simula request HTTP real: el singleton aún no tiene tenant; lo fija el middleware.
    app(CurrentCompany::class)->set(null);

    $this->get(route('company.roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyRoles/Edit')
            ->where('role.id', $role->id));
});

it('sin ser manager Create→Edit es usable con create+update', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo([
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-roles.create'),
        Permission::findOrCreate('company-roles.update'),
    ]);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $this->get(route('company.roles.create'))->assertOk();

    $response = $this->post(route('company.roles.store'), [
        'name' => 'no_manager_custom',
        'description' => 'Con permiso create',
    ]);

    $role = Role::query()->forCompany($this->company->id)->where('name', 'no_manager_custom')->firstOrFail();
    $response->assertRedirect(route('company.roles.edit', $role));

    $this->get(route('company.roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyRoles/Edit')
            ->where('role.name', 'no_manager_custom')
            ->has('matrix'));

    $this->put(route('company.roles.update', $role), [
        'description' => 'Editado sin ser manager',
    ])->assertRedirect();

    $this->patchJson(route('company.roles.permissions.toggle', $role), [
        'permission' => 'budgets.viewAny',
        'enabled' => true,
    ])->assertOk();

    $role->refresh();
    expect($role->description)->toBe('Editado sin ser manager')
        ->and($role->permissions->pluck('name')->all())
        ->toContain('budgets.viewAny', 'sales.access');
});

it('solo company-roles.create sin update no abre Edit', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo([
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-roles.create'),
    ]);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $response = $this->post(route('company.roles.store'), [
        'name' => 'create_only_role',
    ]);

    $role = Role::query()->forCompany($this->company->id)->where('name', 'create_only_role')->firstOrFail();
    $response->assertRedirect(route('company.roles.edit', $role));

    $this->get(route('company.roles.edit', $role))->assertForbidden();
});

it('403 Create/store sin company-roles.create', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->get(route('company.roles.create'))->assertForbidden();
    $this->post(route('company.roles.store'), [
        'name' => 'blocked',
    ])->assertForbidden();
});

it('matriz Edit no incluye AdminOnly ni módulos apagados o no autorizados', function () {
    companyManager();

    $this->post(route('company.roles.store'), [
        'name' => 'matriz_edit',
    ])->assertRedirect();

    $role = Role::query()->forCompany($this->company->id)->where('name', 'matriz_edit')->firstOrFail();

    $this->get(route('company.roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyRoles/Edit')
            ->has('matrix')
            ->where('matrix', function ($matrix) {
                $slugs = collect($matrix)->pluck('slug')->all();

                return ! in_array('configuracion', $slugs, true)
                    && in_array('sales', $slugs, true)
                    && in_array('usuarios', $slugs, true);
            }));

    app(DisableModuleForCompany::class)->handle($this->company, $this->sales);

    $this->get(route('company.roles.edit', $role))
        ->assertInertia(fn ($page) => $page
            ->where('matrix', function ($matrix) {
                return ! collect($matrix)->pluck('slug')->contains('sales');
            }));
});

it('rechaza toggle de permisos fuera de catálogo (AdminOnly / inexistente)', function () {
    companyManager();

    $this->post(route('company.roles.store'), [
        'name' => 'filtrado',
    ])->assertRedirect();

    $role = Role::query()->forCompany($this->company->id)->where('name', 'filtrado')->firstOrFail();

    $this->patchJson(route('company.roles.permissions.toggle', $role), [
        'permission' => 'roles.viewAny',
        'enabled' => true,
    ])->assertInvalid(['permission']);

    $this->patchJson(route('company.roles.permissions.toggle', $role), [
        'permission' => 'no.existe',
        'enabled' => true,
    ])->assertInvalid(['permission']);

    $role->refresh();
    expect($role->permissions)->toHaveCount(0);
});

it('toggle concede access y Functionality completa; custom entra en catálogo', function () {
    companyManager();

    $custom = 'budgets.exportar';
    Permission::findOrCreate($custom);

    $this->post(route('company.roles.store'), [
        'name' => 'toggle_ops',
    ])->assertRedirect();

    $role = Role::query()->forCompany($this->company->id)->where('name', 'toggle_ops')->firstOrFail();

    $this->get(route('company.roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyRoles/Edit')
            ->missing('can_copy')
            ->missing('copy_companies')
            ->where('role.name_readonly', true)
            ->where('matrix', function ($matrix) use ($custom): bool {
                $budgets = collect($matrix)
                    ->flatMap(fn ($mod) => $mod['functionalities'] ?? [])
                    ->firstWhere('slug', 'budgets');

                if ($budgets === null) {
                    return false;
                }

                $names = collect($budgets['permissions'])->pluck('name')->all();

                return in_array($custom, $names, true)
                    && ! in_array('budgets.access', $names, true)
                    && ! in_array('sales.access', $names, true);
            }));

    $this->patchJson(route('company.roles.permissions.toggle', $role), [
        'permission' => 'budgets.viewAny',
        'enabled' => true,
    ])->assertOk()
        ->assertJsonPath('permission_names', fn ($names) => in_array('budgets.viewAny', $names, true)
            && in_array('sales.access', $names, true)
            && ! in_array('budgets.create', $names, true));

    $this->patchJson(route('company.roles.permissions.toggle', $role), [
        'permission' => 'budgets.viewAny',
        'enabled' => false,
    ])->assertOk();

    $role->refresh();
    expect($role->permissions->pluck('name')->all())->not->toContain('budgets.viewAny')
        ->and($role->permissions->pluck('name')->all())->not->toContain('sales.access');

    $this->patchJson(route('company.roles.functionalities.toggle', $role), [
        'functionality' => 'budgets',
        'enabled' => true,
    ])->assertOk();

    $role->refresh();
    expect($role->permissions->pluck('name')->all())
        ->toContain('budgets.viewAny', 'budgets.create', $custom, 'sales.access');
});

it('sin superficie copy tenant (ruta/UI/Action)', function () {
    companyManager();

    expect(collect(Route::getRoutes())->contains(
        fn ($route) => $route->getName() === 'company.roles.copy',
    ))->toBeFalse();

    expect(class_exists(CopyCompanyRoleToCompanies::class))->toBeFalse();

    $source = file_get_contents(resource_path('js/Pages/Admin/CompanyRoles/components/CompanyRoleForm.tsx'));
    expect($source)->not->toContain('rol_copiar')
        ->and($source)->not->toContain('can_copy')
        ->and($source)->not->toContain('/copy');
});

it('rechaza edit/toggle de rol universal por URL', function () {
    companyManager();

    setPermissionsTeamId(null);
    $universal = Role::query()->create([
        'name' => 'universal_tpl',
        'guard_name' => 'web',
        'universal' => true,
    ]);

    $this->get(route('company.roles.edit', $universal))->assertNotFound();
    $this->put(route('company.roles.update', $universal), [
        'description' => 'x',
    ])->assertNotFound();
    $this->patchJson(route('company.roles.permissions.toggle', $universal), [
        'permission' => 'budgets.viewAny',
        'enabled' => true,
    ])->assertNotFound();
});

it('Edit UI usa patchJson y layout plataforma sin mención universal ni copy', function () {
    $source = file_get_contents(resource_path('js/Pages/Admin/CompanyRoles/components/CompanyRoleForm.tsx'));

    expect($source)->toContain('patchJson')
        ->and($source)->toContain('/company/roles/')
        ->and($source)->toContain('permissions')
        ->and($source)->toContain('functionalities')
        ->and($source)->toContain('ScrollToTop')
        ->and($source)->not->toContain('rol_copiar')
        ->and($source)->not->toContain('rol_universal')
        ->and($source)->not->toContain('max-w-4xl');
});

it('bloquea rutas si usuarios no está activo (V-21)', function () {
    companyManager();
    app(DisableModuleForCompany::class)->handle($this->company, $this->usuarios);

    $this->get(route('company.roles.index'))->assertForbidden();
});

it('permite acceso con capas y permiso (V-21)', function () {
    companyManager();

    $this->get(route('company.roles.index'))->assertOk();
});
