<?php

use App\Domains\Admin\Actions\CreateFunctionality;
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
    config(['session.driver' => 'file']);

    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('login como super admin ve Configuración aunque admin no esté encendido', function () {
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

    app(CreateFunctionality::class)->handle([
        'module_id' => $admin->id,
        'name' => 'Functionalities',
        'slug' => 'functionalities',
        'label' => ['es' => 'Funcionalidades', 'en' => 'Functionalities'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    $company = Company::factory()->create([
        'name' => 'ACME Shell',
        'tradename' => 'ACME Shell',
        'slug' => 'acme-shell',
    ]);

    $user = User::factory()->create([
        'name' => 'Super Shell',
        'email' => 'sa-shell@example.com',
        'password' => 'password',
    ]);
    $user->companies()->attach($company->id);

    Role::findOrCreate(Role::SUPER_ADMIN);
    $user->assignRole(Role::SUPER_ADMIN);

    $page = visit('/login');

    $page->fill('email', 'sa-shell@example.com')
        ->fill('password', 'password')
        ->click('Acceder')
        ->assertPathIs('/dashboard')
        ->assertSee('Dashboard')
        ->assertSee('Configuración')
        ->assertSee('ACME Shell')
        ->assertDontSee('Funcionalidades');

    $page->click('Configuración')
        ->assertSee('Módulos')
        ->assertSee('Funcionalidades')
        ->click('Módulos')
        ->assertPathIs('/admin/modules')
        ->assertSee('Módulos')
        ->assertSee('Mostrar/ocultar columnas')
        ->assertSee('Acciones');
});
