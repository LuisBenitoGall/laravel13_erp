<?php

use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/HrTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);
});

it('el seeder registra Module rrhh Optional Active con Functionalities y permisos', function () {
    $this->seed(AdminCatalogSeeder::class);

    $module = Module::query()->where('slug', 'rrhh')->firstOrFail();

    expect($module->level)->toBe(ModuleLevel::Optional)
        ->and($module->status)->toBe(ModuleStatus::Active)
        ->and(Functionality::query()->where('module_id', $module->id)->orderBy('slug')->pluck('slug')->all())
        ->toBe(['contract-types', 'employees'])
        ->and(Functionality::query()->where('module_id', $module->id)->where('status', '!=', FunctionalityStatus::Active)->exists())->toBeFalse();

    $names = Permission::query()->pluck('name');

    foreach (hrPermissions() as $permission) {
        expect($names)->toContain($permission);
    }

    // Optional: la company demo NO queda autorizada automáticamente.
    $acme = Company::query()->where('slug', 'acme')->firstOrFail();
    expect(CompanyModuleAuthorization::query()->withoutGlobalScope('company')->where('company_id', $acme->id)->where('module_id', $module->id)->exists())->toBeFalse();
});

it('menú RRHH: capas + comodín + .view; Super Admin ve ambos ítems con su href', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::query()->where('slug', 'acme')->firstOrFail();
    $module = Module::query()->where('slug', 'rrhh')->firstOrFail();

    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $company);

    // Sin autorizar: RRHH no aparece (Optional exige autorizado + encendido).
    expect(collect(app(MenuBuilder::class)->forUser($sa))->pluck('slug'))->not->toContain('rrhh');

    enableModuleForCompany($company, 'rrhh');

    $rrhh = collect(app(MenuBuilder::class)->forUser($sa))->firstWhere('slug', 'rrhh');
    $items = collect($rrhh['functionalities'] ?? [])->keyBy('slug');

    expect($items->keys()->all())->toBe(['contract-types', 'employees'])
        ->and($items['contract-types']['href'])->toBe(route('hr.contract-types.index', absolute: false))
        ->and($items['employees']['href'])->toBe(route('hr.employees.index', absolute: false));

    // Usuario normal: comodín AND .view.
    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(['rrhh.access', 'employees.view']);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $rrhh = collect(app(MenuBuilder::class)->forUser($user))->firstWhere('slug', 'rrhh');

    expect(collect($rrhh['functionalities'])->pluck('slug')->all())->toBe(['employees']);

    // .view sin comodín → sin menú RRHH.
    $user->revokePermissionTo('rrhh.access');
    $user->unsetRelation('permissions');

    expect(collect(app(MenuBuilder::class)->forUser($user))->pluck('slug'))->not->toContain('rrhh');

    // Apagado el módulo, tampoco para SA.
    app(DisableModuleForCompany::class)->handle($company, $module);

    expect(collect(app(MenuBuilder::class)->forUser($sa))->pluck('slug'))->not->toContain('rrhh');
});

it('manager de una company autorizada recibe los permisos de RRHH', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::factory()->create();
    app(AuthorizeModuleForCompany::class)->handle($company, Module::query()->where('slug', 'rrhh')->firstOrFail());
    app(SyncManagerPermissionsForCompany::class)->handle($company);

    setPermissionsTeamId($company->id);
    $manager = Role::query()->where('name', 'manager')->where('company_id', $company->id)->firstOrFail();

    foreach (hrPermissions() as $permission) {
        expect($manager->hasPermissionTo($permission))->toBeTrue();
    }
});

dataset('hr routes', [
    'contract-types index' => ['hr.contract-types.index', 'contract-types.view'],
    'contract-types table' => ['hr.contract-types.table', 'contract-types.view'],
    'employees index' => ['hr.employees.index', 'employees.view'],
    'employees table' => ['hr.employees.table', 'employees.view'],
]);

it('V-21 bloquea el acceso si el módulo rrhh está apagado para la company', function (string $route) {
    $company = Company::factory()->create();
    actingHrUser($company);

    app(DisableModuleForCompany::class)->handle($company, Module::query()->where('slug', 'rrhh')->firstOrFail());

    $this->get(route($route))->assertForbidden();
})->with('hr routes');

it('V-21 bloquea el acceso si rrhh no está autorizado para la company', function (string $route) {
    ensureHrCatalog();
    $company = Company::factory()->create();

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(collect(hrPermissions())->map(fn ($n) => Permission::findOrCreate($n))->all());

    $this->get(route($route))->assertForbidden();
})->with('hr routes');

it('V-21 bloquea si el catálogo rrhh está Inactive (bloqueo global)', function (string $route) {
    $company = Company::factory()->create();
    actingHrUser($company);

    Module::query()->where('slug', 'rrhh')->update(['status' => ModuleStatus::Inactive]);

    $this->get(route($route))->assertForbidden();
})->with('hr routes');

it('V-21 niega acceso sin permiso de la Functionality', function (string $route, string $permission) {
    $company = Company::factory()->create();
    $user = actingHrUser($company);

    $user->revokePermissionTo($permission);
    $user->unsetRelation('permissions');

    $this->get(route($route))->assertForbidden();
})->with('hr routes');

it('V-21 permite acceso con capas satisfechas y permiso', function (string $route) {
    $company = Company::factory()->create();
    actingHrUser($company);

    $this->get(route($route))->assertOk();
})->with('hr routes');
