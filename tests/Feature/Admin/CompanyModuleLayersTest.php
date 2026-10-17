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

    $company = app(CreateCompany::class)->handle([
        'name' => 'Co',
        'slug' => 'co',
        'tradename' => null,
        'nif' => null,
        'status' => 'active',
        'logo' => null,
    ], User::factory()->create());

    $module = Module::query()->where('slug', 'core')->firstOrFail();
    app(EnableModuleForCompany::class)->handle($company, $module);

    withCurrentCompany($company);
    expect(app(CompanyModuleActivationChecker::class)->isActive('core'))->toBeTrue();
});

it('menú respeta módulo apagado aunque haya permiso', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    app(AuthorizeModuleForCompany::class)->handle($company, $module);
    // no enable

    $user = User::factory()->create();
    Permission::findOrCreate('admin.access');
    $user->givePermissionTo('admin.access');
    withCurrentCompany($company);

    expect(app(MenuBuilder::class)->forUser($user))->toBeEmpty()
        ->and(app(CompanyModuleActivationChecker::class)->isActive('admin'))->toBeFalse();
});

it('menú muestra módulo autorizado y encendido con permiso', function () {
    $company = Company::factory()->create();
    $module = Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    app(CreateFunctionality::class)->handle([
        'module_id' => $module->id,
        'name' => 'Modules',
        'slug' => 'modules',
        'label' => ['es' => 'Módulos', 'en' => 'Modules'],
        'status' => 'active',
    ]);
    enableModuleForCompany($company, 'admin');

    $user = User::factory()->create();
    Permission::findOrCreate('modules.viewAny');
    $user->givePermissionTo('modules.viewAny');
    withCurrentCompany($company);

    $menu = app(MenuBuilder::class)->forUser($user);

    expect($menu)->toHaveCount(1)
        ->and($menu[0]['slug'])->toBe('admin');
});

it('ruta module:admin responde 403 si el módulo está apagado', function () {
    $company = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    app(AuthorizeModuleForCompany::class)->handle($company, Module::query()->where('slug', 'admin')->firstOrFail());

    $user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $user->assignRole('Super Admin');
    actingWithCompany($user, $company);

    $this->get(route('admin.modules.index'))->assertForbidden();
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
