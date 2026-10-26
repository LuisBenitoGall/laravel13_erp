<?php

use App\Domains\Admin\Actions\AssignCompanyRole;
use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->company = Company::factory()->create();

    $admin = Module::factory()->create([
        'slug' => 'admin',
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

    $this->workspace = app(CreateModule::class)->handle([
        'name' => 'Workspace',
        'slug' => 'workspace',
        'label' => ['es' => 'Espacio de trabajo', 'en' => 'Workspace'],
        'color' => '#0369a1',
        'icon' => 'briefcase',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->workspace->id,
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
    app(AuthorizeModuleForCompany::class)->handle($this->company, $this->workspace);
    app(EnableModuleForCompany::class)->handle($this->company, $this->workspace);
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
        'permissions' => ['budgets.viewAny', 'budgets.view'],
    ])->assertRedirect();

    $role = Role::query()->forCompany($this->company->id)->where('name', 'vendedor')->first();
    expect($role)->not->toBeNull()
        ->and($role->universal)->toBeFalse()
        ->and((int) $role->company_id)->toBe((int) $this->company->id)
        ->and($role->permissions->pluck('name')->all())->toEqualCanonicalizing(['budgets.viewAny', 'budgets.view']);

    $this->put(route('company.roles.update', $role), [
        'description' => 'Ventas 2',
        'permissions' => ['budgets.viewAny'],
    ])->assertRedirect();

    $role->refresh();
    expect($role->description)->toBe('Ventas 2')
        ->and($role->permissions->pluck('name')->all())->toBe(['budgets.viewAny']);

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

it('niega CRUD a usuario sin ser manager', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->get(route('company.roles.index'))->assertForbidden();
    $this->post(route('company.roles.store'), [
        'name' => 'x',
    ])->assertForbidden();
});

it('guest no administra roles aunque el módulo esté activo', function () {
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

it('ignora permissions del manager local y guarda description (cuerpo UI)', function () {
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
});

it('permite editar description del manager sin tocar permissions', function () {
    companyManager();

    $manager = Role::query()->forCompany($this->company->id)->where('name', Role::MANAGER)->firstOrFail();

    $this->put(route('company.roles.update', $manager), [
        'description' => 'Manager desc',
    ])->assertRedirect();

    $manager->refresh();
    expect($manager->description)->toBe('Manager desc');
});

it('permite editar permisos de guest local', function () {
    companyManager();

    $guest = Role::query()->forCompany($this->company->id)->where('name', Role::GUEST)->firstOrFail();

    $this->put(route('company.roles.update', $guest), [
        'permissions' => ['budgets.viewAny'],
    ])->assertRedirect();

    $guest->refresh();
    expect($guest->permissions->pluck('name')->all())->toBe(['budgets.viewAny']);
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
    app(AuthorizeModuleForCompany::class)->handle($otherCompany, $this->workspace);
    app(EnableModuleForCompany::class)->handle($otherCompany, $this->workspace);
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

it('matriz no incluye AdminOnly ni módulos apagados o no autorizados', function () {
    companyManager();

    $this->get(route('company.roles.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyRoles/Create')
            ->has('matrix')
            ->where('matrix', function ($matrix) {
                $slugs = collect($matrix)->pluck('slug')->all();

                return ! in_array('admin', $slugs, true)
                    && in_array('sales', $slugs, true)
                    && in_array('workspace', $slugs, true);
            }));

    app(DisableModuleForCompany::class)->handle($this->company, $this->sales);

    $this->get(route('company.roles.create'))
        ->assertInertia(fn ($page) => $page
            ->where('matrix', function ($matrix) {
                return ! collect($matrix)->pluck('slug')->contains('sales');
            }));
});

it('filtra permisos fuera de catálogo al guardar', function () {
    companyManager();

    $this->post(route('company.roles.store'), [
        'name' => 'filtrado',
        'permissions' => ['budgets.viewAny', 'roles.viewAny', 'no.existe'],
    ])->assertRedirect();

    $role = Role::query()->forCompany($this->company->id)->where('name', 'filtrado')->firstOrFail();
    expect($role->permissions->pluck('name')->all())->toBe(['budgets.viewAny']);
});

it('bloquea rutas si workspace no está activo (V-21)', function () {
    companyManager();
    app(DisableModuleForCompany::class)->handle($this->company, $this->workspace);

    $this->get(route('company.roles.index'))->assertForbidden();
});

it('permite acceso con capas y permiso (V-21)', function () {
    companyManager();

    $this->get(route('company.roles.index'))->assertOk();
});
