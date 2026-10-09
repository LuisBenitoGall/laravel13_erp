<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->company = Company::factory()->create();

    $this->module = app(CreateModule::class)->handle([
        'name' => 'Admin',
        'slug' => 'admin',
        'label' => ['es' => 'Admin', 'en' => 'Admin'],
        'color' => '#0f766e',
        'icon' => 'settings',
        'level' => ModuleLevel::AdminOnly->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->module->id,
        'name' => 'Modules',
        'slug' => 'modules',
        'label' => ['es' => 'Módulos', 'en' => 'Modules'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->module->id,
        'name' => 'Functionalities',
        'slug' => 'functionalities',
        'label' => ['es' => 'Funcionalidades', 'en' => 'Functionalities'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'admin');
    withCurrentCompany($this->company);
});

it('muestra módulo con solo viewAny de Functionality sin comodín', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('modules.viewAny');
    $user->givePermissionTo('modules.viewAny');

    $menu = app(MenuBuilder::class)->forUser($user);

    expect($menu)->toHaveCount(1)
        ->and($menu[0]['slug'])->toBe('admin')
        ->and(collect($menu[0]['functionalities'])->pluck('slug')->all())->toBe(['modules']);
});

it('comodín implica todas las Functionalities del módulo', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('admin.access');
    $user->givePermissionTo('admin.access');

    $menu = app(MenuBuilder::class)->forUser($user);

    expect($menu)->toHaveCount(1)
        ->and(collect($menu[0]['functionalities'])->pluck('slug')->sort()->values()->all())
        ->toBe(['functionalities', 'modules']);
});

it('módulo solo visible si elegible y encendido', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('admin.access');
    $user->givePermissionTo('admin.access');

    app(DisableModuleForCompany::class)
        ->handle($this->company, $this->module);

    expect(app(MenuBuilder::class)->forUser($user))->toBeEmpty();
});
