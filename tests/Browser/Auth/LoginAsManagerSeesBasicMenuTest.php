<?php

use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\CreateCompany;
use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'file']);

    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('login como manager entra al AppLayout con menú Basic usable', function () {
    $module = app(CreateModule::class)->handle([
        'name' => 'Partners Core',
        'slug' => 'partners-core',
        'label' => ['es' => 'Partners Core', 'en' => 'Partners Core'],
        'color' => '#0f766e',
        'icon' => 'users',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
        'explanation' => ['es' => 'Módulo Basic de prueba', 'en' => 'Basic test module'],
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $module->id,
        'name' => 'Workplaces',
        'slug' => 'workplaces',
        'label' => ['es' => 'Centros de trabajo', 'en' => 'Workplaces'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $manager = User::factory()->create([
        'name' => 'Usuario Manager',
        'email' => 'manager-menu@example.com',
        'password' => 'password',
    ]);

    Account::factory()->default()->create(['status' => AccountStatus::Active]);

    $company = app(CreateCompany::class)->handle([
        'name' => 'Empresa Manager',
        'slug' => 'empresa-manager',
        'tradename' => 'Empresa Manager',
        'nif' => null,
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ], $manager);

    app(AuthorizeModuleForCompany::class)->handle($company, $module);

    setPermissionsTeamId($company->id);
    $manager->givePermissionTo(['partners-core.access', 'workplaces.view']);
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $page = visit('/login');

    $page->fill('#d-login-email', 'manager-menu@example.com')
        ->fill('#d-login-password', 'password')
        ->click('form:has(#d-login-email) button[type="submit"]')
        ->assertPathIs('/dashboard')
        ->assertSee('Mis opciones preferidas')
        ->assertSee('Dashboard')
        ->assertSee('Empresa Manager')
        ->assertSee('UM')
        ->click('UM')
        ->assertSee('Perfil')
        ->assertSee('Salir')
        ->assertSee('Centros de trabajo')
        ->assertDontSee('Módulos');
});
