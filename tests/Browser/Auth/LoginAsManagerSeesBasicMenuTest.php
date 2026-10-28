<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Partners\Actions\CreateCompany;
use App\Domains\Partners\Enums\CompanyStatus;
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
        'name' => 'Demo catalog',
        'slug' => 'demo-catalog',
        'label' => ['es' => 'Catálogo demo', 'en' => 'Catálogo demo'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $manager = User::factory()->create([
        'name' => 'Usuario Manager',
        'email' => 'manager-menu@example.com',
        'password' => 'password',
    ]);

    $company = app(CreateCompany::class)->handle([
        'name' => 'Empresa Manager',
        'slug' => 'empresa-manager',
        'tradename' => 'Empresa Manager',
        'nif' => null,
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ], $manager);

    app(EnableModuleForCompany::class)->handle($company, $module);

    $page = visit('/login');

    $page->fill('email', 'manager-menu@example.com')
        ->fill('password', 'password')
        ->click('Acceder')
        ->assertPathIs('/dashboard')
        ->assertSee('Inicio')
        ->assertSee('Dashboard')
        ->assertSee('Empresa Manager')
        ->assertSee('UM')
        ->click('UM')
        ->assertSee('Perfil')
        ->assertSee('Salir')
        ->assertSee('Catálogo demo')
        ->assertDontSee('Configuración')
        ->assertDontSee('Módulos');
});
