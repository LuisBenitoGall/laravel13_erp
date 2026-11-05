<?php

use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModule;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\CompanyModuleActivationChecker;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Partners\Actions\CreateCompany;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
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

it('módulo Basic autorizado por defecto al crear company', function () {
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
    app(EnableModuleForCompany::class)->handle($company, $module);

    Permission::findOrCreate('core.access');
    $user->givePermissionTo('core.access');
    actingWithCompany($user, $company);

    expect(app(CompanyModuleActivationChecker::class)->isActive('core'))->toBeTrue();
});

it('menú respeta módulo apagado aunque haya permiso', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'tools',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    app(AuthorizeModuleForCompany::class)->handle($company, $module);
    // no enable

    $user = User::factory()->create();
    Permission::findOrCreate('tools.access');
    $user->givePermissionTo('tools.access');
    withCurrentCompany($company);

    expect(collect(app(MenuBuilder::class)->forUser($user))->pluck('slug')->all())->toBe([])
        ->and(app(CompanyModuleActivationChecker::class)->isActive('tools'))->toBeFalse();
});

it('menú muestra módulo autorizado y encendido con permiso AND', function () {
    $company = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'dashboard',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    $module = Module::factory()->create([
        'slug' => 'tools',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    app(CreateFunctionality::class)->handle([
        'module_id' => $module->id,
        'name' => 'Tools list',
        'slug' => 'tools-list',
        'label' => ['es' => 'Herramientas', 'en' => 'Tools'],
        'status' => 'active',
    ]);
    enableModuleForCompany($company, 'dashboard');
    enableModuleForCompany($company, 'tools');

    $user = User::factory()->create();
    Permission::findOrCreate('tools.access');
    Permission::findOrCreate('tools-list.viewAny');
    Permission::findOrCreate('dashboard.access');
    $user->givePermissionTo(['tools.access', 'tools-list.viewAny', 'dashboard.access']);
    withCurrentCompany($company);

    $menu = app(MenuBuilder::class)->forUser($user);

    expect(collect($menu)->pluck('slug')->all())->toContain('dashboard', 'tools');
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

it('encender y apagar persiste en company_modules', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'tools',
        'level' => ModuleLevel::Basic,
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
