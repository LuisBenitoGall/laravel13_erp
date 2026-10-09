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

it('login con N empresas exige selección bloqueante antes del ERP', function () {
    $alpha = Company::factory()->create([
        'name' => 'Alpha Corp',
        'tradename' => 'Alpha Corp',
        'slug' => 'alpha-corp',
    ]);
    $beta = Company::factory()->create([
        'name' => 'Beta Corp',
        'tradename' => 'Beta Corp',
        'slug' => 'beta-corp',
    ]);
    enableModuleForCompany($alpha, 'admin');
    enableModuleForCompany($beta, 'admin');

    $user = User::factory()->create([
        'name' => 'Usuario Multi',
        'email' => 'multi-company@example.com',
        'password' => 'password',
    ]);
    $user->companies()->attach([$alpha->id, $beta->id]);

    Role::findOrCreate('Super Admin');
    $user->assignRole('Super Admin');

    $page = visit('/login');

    $page->fill('email', 'multi-company@example.com')
        ->fill('password', 'password')
        ->click('Acceder')
        ->assertPathIs('/companies/select')
        ->assertSee('Seleccionar empresa')
        ->assertSee('Continuar')
        ->assertDontSee('Salir');

    $page->select('company_id', (string) $beta->id)
        ->click('Continuar')
        ->assertPathIs('/admin/modules')
        ->assertSee('Módulos')
        ->assertSee('Salir')
        ->assertSee('Beta Corp')
        ->assertSee('Usuario Multi');
});
