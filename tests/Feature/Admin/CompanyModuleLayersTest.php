<?php

use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\CreateCompany;
use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\CompanyModuleActivationChecker;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    MenuCatalogCache::forget();

    if (! Account::query()->where('is_default', true)->exists()) {
        Account::factory()->default()->create(['status' => AccountStatus::Active]);
    }
});

it('bloquea Module global Inactive', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'sales',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Inactive,
    ]);
    app(AuthorizeModuleForCompany::class)->handle($company, $module);
    withCurrentCompany($company);

    expect(app(CompanyModuleActivationChecker::class)->isActive('sales'))->toBeFalse();
});

it('no permite encender módulo no autorizado', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'optional-mod',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);

    expect(fn () => app(EnableModuleForCompany::class)->handle($company, $module))
        ->toThrow(RuntimeException::class);
});

it('rechaza enable disable de Basic y AdminOnly', function () {
    $company = Company::factory()->create();
    $basic = Module::factory()->create([
        'slug' => 'core-basic',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    $adminOnly = Module::factory()->create([
        'slug' => 'cfg-admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    app(AuthorizeModuleForCompany::class)->handle($company, $basic);
    app(AuthorizeModuleForCompany::class)->handle($company, $adminOnly);

    expect(fn () => app(EnableModuleForCompany::class)->handle($company, $basic))
        ->toThrow(RuntimeException::class)
        ->and(fn () => app(DisableModuleForCompany::class)->handle($company, $basic))
        ->toThrow(RuntimeException::class)
        ->and(fn () => app(EnableModuleForCompany::class)->handle($company, $adminOnly))
        ->toThrow(RuntimeException::class)
        ->and(fn () => app(DisableModuleForCompany::class)->handle($company, $adminOnly))
        ->toThrow(RuntimeException::class);
});

it('módulo Basic autorizado por defecto al crear company sin company_modules', function () {
    Module::factory()->create([
        'slug' => 'core',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);

    $user = User::factory()->create();
    $company = app(CreateCompany::class)->handle([
        'name' => 'Co',
        'slug' => 'co',
        'tradename' => null,
        'nif' => null,
        'status' => 'active',
        'logo' => null,
    ], $user);

    $module = Module::query()->where('slug', 'core')->firstOrFail();

    expect(
        CompanyModule::query()->withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->where('module_id', $module->id)
            ->exists(),
    )->toBeFalse();

    Permission::findOrCreate('core.access');
    $user->givePermissionTo('core.access');
    actingWithCompany($user, $company);

    expect(app(CompanyModuleActivationChecker::class)->isActive('core'))->toBeTrue();
});

it('Basic autorizado sigue on aunque exista fila company_modules apagada histórica', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'tools',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    app(AuthorizeModuleForCompany::class)->handle($company, $module);
    // Fila huérfana histórica (antes se podía «apagar» Basic): checker la ignora.
    CompanyModule::query()->withoutGlobalScope('company')->create([
        'company_id' => $company->id,
        'module_id' => $module->id,
    ]);
    CompanyModule::query()->withoutGlobalScope('company')
        ->where('company_id', $company->id)
        ->where('module_id', $module->id)
        ->delete();

    $user = User::factory()->create();
    Permission::findOrCreate('tools.access');
    $user->givePermissionTo('tools.access');
    actingWithCompany($user, $company);
    MenuCatalogCache::forget();

    expect(app(CompanyModuleActivationChecker::class)->isActive('tools'))->toBeTrue()
        ->and(collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all())
        ->toContain('tools');
});

it('Optional apagado no entra en menú aunque haya permiso', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'optional-tools',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);
    app(AuthorizeModuleForCompany::class)->handle($company, $module);
    // no enable

    $user = User::factory()->create();
    Permission::findOrCreate('optional-tools.access');
    $user->givePermissionTo('optional-tools.access');
    withCurrentCompany($company);
    MenuCatalogCache::forget();

    expect(collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all())->toBe([])
        ->and(app(CompanyModuleActivationChecker::class)->isActive('optional-tools'))->toBeFalse();
});

it('menú muestra Basic autorizado y Optional autorizado+encendido con permiso AND', function () {
    $company = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'dashboard',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'slug' => 'tools',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'slug' => 'extra-opt',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($company, 'dashboard');
    enableModuleForCompany($company, 'tools');
    enableModuleForCompany($company, 'extra-opt');
    MenuCatalogCache::forget();

    $user = User::factory()->create();
    Permission::findOrCreate('tools.access');
    Permission::findOrCreate('dashboard.access');
    Permission::findOrCreate('extra-opt.access');
    $user->givePermissionTo(['tools.access', 'dashboard.access', 'extra-opt.access']);
    withCurrentCompany($company);

    $menu = app(MenuBuilder::class)->forUser($user);

    expect(collect($menu)->pluck('slug')->all())->toContain('dashboard', 'tools', 'extra-opt');
});

it('SA accede a module:configuracion aunque el módulo esté apagado', function () {
    $company = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'configuracion',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);

    $user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $user->assignRole('Super Admin');
    actingWithCompany($user, $company);

    $this->get(route('admin.modules.index'))->assertOk();
});

it('encender y apagar Optional persiste en company_modules', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'opt-tools',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);
    app(AuthorizeModuleForCompany::class)->handle($company, $module);
    app(EnableModuleForCompany::class)->handle($company, $module);

    expect(
        CompanyModule::query()->withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->where('module_id', $module->id)
            ->exists()
    )->toBeTrue();

    app(DisableModuleForCompany::class)->handle($company, $module);

    expect(
        CompanyModule::query()->withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->where('module_id', $module->id)
            ->exists()
    )->toBeFalse();
});

it('Mis módulos Index solo lista Optional Active', function () {
    $company = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'cuenta',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    $optional = Module::factory()->create([
        'slug' => 'opt-visible',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'slug' => 'basic-hidden',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'slug' => 'admin-hidden',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'slug' => 'opt-inactive',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Inactive,
    ]);
    enableModuleForCompany($company, 'cuenta');
    MenuCatalogCache::forget();

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    Permission::findOrCreate('company-modules.view');
    Permission::findOrCreate('cuenta.access');
    $user->givePermissionTo(['company-modules.view', 'cuenta.access']);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');
    setPermissionsTeamId(null);

    $this->get(route('admin.company-modules.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CompanyModules/Index')
            ->has('modules', 1)
            ->where('modules.0.id', $optional->id)
            ->where('modules.0.slug', 'opt-visible')
            ->where('modules.0.level', ModuleLevel::Optional->value));
});
