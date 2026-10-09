<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'file']);

    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
});

it('login con una empresa entra al AppLayout', function () {
    $company = Company::factory()->create([
        'name' => 'Empresa Una',
        'tradename' => 'Empresa Una',
        'slug' => 'empresa-una',
    ]);
    enableModuleForCompany($company, 'admin');

    $user = User::factory()->create([
        'name' => 'Usuario Una',
        'email' => 'one-company@example.com',
        'password' => 'password',
    ]);
    $user->companies()->attach($company->id);

    Role::findOrCreate('Super Admin');
    $user->assignRole('Super Admin');

    $page = visit('/login');

    $page->fill('email', 'one-company@example.com')
        ->fill('password', 'password')
        ->click('Acceder')
        ->assertPathIs('/admin/modules')
        ->assertSee('Módulos')
        ->assertSee('Salir')
        ->assertSee('Empresa Una')
        ->assertSee('Usuario Una');
});
