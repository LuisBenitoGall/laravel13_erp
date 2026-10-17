<?php

use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Actions\RevokeModuleAuthorizationForCompany;
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
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->creator = User::factory()->create();
});

it('autorizar Optional añade permisos al manager', function () {
    $company = app(CreateCompany::class)->handle([
        'name' => 'Sync Co',
        'slug' => 'sync-co',
        'tradename' => null,
        'nif' => null,
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ], $this->creator);

    $optional = Module::factory()->create([
        'slug' => 'premium',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $optional->id,
        'name' => 'Premium tools',
        'slug' => 'premium-tools',
        'label' => ['es' => 'Premium', 'en' => 'Premium'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    app(AuthorizeModuleForCompany::class)->handle($company, $optional);

    setPermissionsTeamId($company->id);
    $manager = Role::findByName('manager');

    expect($manager->hasPermissionTo('premium-tools.viewAny'))->toBeTrue();
});

it('desautorizar quita permisos del manager', function () {
    $company = app(CreateCompany::class)->handle([
        'name' => 'Revoke Co',
        'slug' => 'revoke-co',
        'tradename' => null,
        'nif' => null,
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ], $this->creator);

    $optional = Module::factory()->create([
        'slug' => 'addons',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $optional->id,
        'name' => 'Addons',
        'slug' => 'addons-ui',
        'label' => ['es' => 'Addons', 'en' => 'Addons'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    app(AuthorizeModuleForCompany::class)->handle($company, $optional);
    app(RevokeModuleAuthorizationForCompany::class)->handle($company, $optional);

    setPermissionsTeamId($company->id);
    $manager = Role::findByName('manager');

    expect($manager->hasPermissionTo('addons-ui.viewAny'))->toBeFalse();
});

it('AdminOnly no entra en el sync del manager', function () {
    $company = Company::factory()->create();
    app(EnsureCompanyDefaultRoles::class)->handle($company);

    $admin = Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $admin->id,
        'name' => 'Modules',
        'slug' => 'modules',
        'label' => ['es' => 'Módulos', 'en' => 'Modules'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    app(AuthorizeModuleForCompany::class)->handle($company, $admin);
    app(SyncManagerPermissionsForCompany::class)->handle($company);

    setPermissionsTeamId($company->id);
    $manager = Role::findByName('manager');

    expect($manager->hasPermissionTo('modules.viewAny'))->toBeFalse();
});

it('encender o apagar no cambia role_has_permissions del manager', function () {
    Module::factory()->create([
        'slug' => 'core',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => Module::query()->where('slug', 'core')->value('id'),
        'name' => 'Core UI',
        'slug' => 'core-ui',
        'label' => ['es' => 'Core', 'en' => 'Core'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $company = app(CreateCompany::class)->handle([
        'name' => 'Toggle Co',
        'slug' => 'toggle-co',
        'tradename' => null,
        'nif' => null,
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ], $this->creator);

    $module = Module::query()->where('slug', 'core')->firstOrFail();
    setPermissionsTeamId($company->id);
    $manager = Role::findByName('manager');
    $before = DB::table('role_has_permissions')->where('role_id', $manager->id)->pluck('permission_id')->sort()->values()->all();

    app(EnableModuleForCompany::class)->handle($company, $module);
    $afterEnable = DB::table('role_has_permissions')->where('role_id', $manager->id)->pluck('permission_id')->sort()->values()->all();

    app(DisableModuleForCompany::class)->handle($company, $module);
    $afterDisable = DB::table('role_has_permissions')->where('role_id', $manager->id)->pluck('permission_id')->sort()->values()->all();

    expect($afterEnable)->toBe($before)
        ->and($afterDisable)->toBe($before);
});
