<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Actions\CreateCompany;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
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
    enableModuleForCompany($this->company, 'admin');

    app(CreateFunctionality::class)->handle([
        'module_id' => $admin->id,
        'name' => 'Roles',
        'slug' => 'roles',
        'label' => ['es' => 'Roles', 'en' => 'Roles'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $this->basic = app(CreateModule::class)->handle([
        'name' => 'Sales',
        'slug' => 'sales',
        'label' => ['es' => 'Ventas', 'en' => 'Sales'],
        'color' => '#0369a1',
        'icon' => 'cart',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->basic->id,
        'name' => 'Budgets',
        'slug' => 'budgets',
        'label' => ['es' => 'Presupuestos', 'en' => 'Budgets'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    Role::findOrCreate(Role::SUPER_ADMIN);
    foreach ([Role::MANAGER, Role::GUEST] as $name) {
        $tpl = Role::findOrCreate($name);
        $tpl->forceFill(['universal' => true])->save();
    }
});

function platformAdmin(): User
{
    $user = User::factory()->create();
    setPermissionsTeamId(null);
    $user->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($user, test()->company);

    return $user;
}

it('permite CRUD de rol de plataforma al Super Admin', function () {
    platformAdmin();

    $this->post(route('admin.roles.store'), [
        'name' => 'operator',
        'description' => 'Ops',
        'universal' => false,
        'permissions' => ['roles.viewAny', 'budgets.viewAny'],
    ])->assertRedirect();

    $role = Role::query()->platform()->where('name', 'operator')->first();
    expect($role)->not->toBeNull()
        ->and($role->universal)->toBeFalse()
        ->and($role->permissions->pluck('name')->all())->toContain('roles.viewAny')
        ->and($role->permissions->pluck('name')->all())->toContain('budgets.viewAny');

    $this->put(route('admin.roles.update', $role), [
        'description' => 'Ops 2',
        'universal' => true,
        'permissions' => ['budgets.viewAny'],
    ])->assertRedirect();

    $role->refresh();
    expect($role->description)->toBe('Ops 2')
        ->and($role->universal)->toBeTrue()
        ->and($role->permissions->pluck('name')->all())->toBe(['budgets.viewAny']);

    $this->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles.index'));
    expect(Role::query()->platform()->where('name', 'operator')->exists())->toBeFalse();
});

it('niega CRUD de roles a usuario no Super Admin', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->get(route('admin.roles.index'))->assertForbidden();
    $this->post(route('admin.roles.store'), [
        'name' => 'x',
        'universal' => false,
    ])->assertForbidden();
});

it('rechaza borrar plantillas manager y guest y no permite Super Admin en CRUD', function () {
    platformAdmin();

    $manager = Role::query()->platform()->where('name', Role::MANAGER)->firstOrFail();
    $guest = Role::query()->platform()->where('name', Role::GUEST)->firstOrFail();

    $this->delete(route('admin.roles.destroy', $manager))->assertSessionHasErrors('role');
    $this->delete(route('admin.roles.destroy', $guest))->assertSessionHasErrors('role');

    $super = Role::findOrCreate(Role::SUPER_ADMIN);
    $this->get(route('admin.roles.edit', $super))->assertNotFound();
});

it('rechaza borrar rol con usuarios asignados', function () {
    platformAdmin();

    $role = Role::create([
        'name' => 'with_users',
        'guard_name' => 'web',
        'universal' => false,
    ]);

    $other = User::factory()->create();
    setPermissionsTeamId(null);
    $other->assignRole($role);

    $this->delete(route('admin.roles.destroy', $role))->assertSessionHasErrors('role');
    expect(Role::query()->whereKey($role->id)->exists())->toBeTrue();
});

it('universales no guardan permisos AdminOnly; genéricos sí', function () {
    platformAdmin();

    $this->post(route('admin.roles.store'), [
        'name' => 'uni_ops',
        'universal' => true,
        'permissions' => ['roles.viewAny', 'budgets.viewAny'],
    ])->assertRedirect();

    $uni = Role::query()->platform()->where('name', 'uni_ops')->firstOrFail();
    expect($uni->permissions->pluck('name')->all())->toBe(['budgets.viewAny']);

    $this->post(route('admin.roles.store'), [
        'name' => 'plat_ops',
        'universal' => false,
        'permissions' => ['roles.viewAny', 'budgets.viewAny'],
    ])->assertRedirect();

    $plat = Role::query()->platform()->where('name', 'plat_ops')->firstOrFail();
    expect($plat->permissions->pluck('name')->all())->toContain('roles.viewAny')
        ->and($plat->permissions->pluck('name')->all())->toContain('budgets.viewAny');
});

it('plantilla manager no altera permisos al update de matriz', function () {
    platformAdmin();

    $manager = Role::query()->platform()->where('name', Role::MANAGER)->firstOrFail();
    $manager->syncPermissions([]);

    $this->put(route('admin.roles.update', $manager), [
        'description' => 'Manager template',
        'permissions' => ['budgets.viewAny', 'budgets.create'],
    ])->assertRedirect();

    $manager->refresh();
    expect($manager->permissions)->toHaveCount(0)
        ->and($manager->description)->toBe('Manager template');
});

it('crear company materializa universales y copia permisos excepto manager', function () {
    setPermissionsTeamId(null);
    $extra = Role::create([
        'name' => 'auditor',
        'guard_name' => 'web',
        'universal' => true,
        'description' => 'Audit',
    ]);
    $extra->syncPermissions(Permission::findOrCreate('budgets.view'));

    $creator = User::factory()->create();
    $company = app(CreateCompany::class)->handle([
        'name' => 'New Co',
        'slug' => 'new-co-'.uniqid(),
        'tradename' => 'New',
        'nif' => 'B'.random_int(10000000, 99999999),
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ], $creator);

    setPermissionsTeamId($company->id);
    expect(Role::findByName(Role::MANAGER))->not->toBeNull()
        ->and(Role::findByName(Role::GUEST))->not->toBeNull()
        ->and(Role::findByName('auditor')->permissions->pluck('name')->all())->toContain('budgets.view');

    setPermissionsTeamId(null);
    $after = Role::create([
        'name' => 'late_universal',
        'guard_name' => 'web',
        'universal' => true,
    ]);

    setPermissionsTeamId($company->id);
    expect(Role::query()->where('name', 'late_universal')->where('company_id', $company->id)->exists())->toBeFalse();

    Artisan::call('roles:materialize-universals');

    setPermissionsTeamId($company->id);
    expect(Role::findByName('late_universal'))->not->toBeNull();

    // silence unused
    expect($after->id)->toBeGreaterThan(0);
});

it('comando backfill es idempotente y sync manager sigue intacto', function () {
    platformAdmin();
    $creator = User::factory()->create();
    $company = app(CreateCompany::class)->handle([
        'name' => 'Sync Co',
        'slug' => 'sync-co-'.uniqid(),
        'tradename' => 'Sync',
        'nif' => 'B'.random_int(10000000, 99999999),
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ], $creator);

    Artisan::call('roles:materialize-universals');
    Artisan::call('roles:materialize-universals');

    setPermissionsTeamId($company->id);
    $manager = Role::findByName(Role::MANAGER);
    $before = $manager->permissions->pluck('name')->sort()->values()->all();

    app(SyncManagerPermissionsForCompany::class)->handle($company);
    $manager->refresh();
    expect($manager->permissions->pluck('name')->sort()->values()->all())->toBe($before);

    app(EnsureCompanyDefaultRoles::class)->handle($company);
    setPermissionsTeamId($company->id);
    $guest = Role::findByName(Role::GUEST);
    expect($guest->permissions)->toHaveCount(0);
});
