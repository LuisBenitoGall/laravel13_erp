<?php

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
        ->assertPathIs('/dashboard')
        ->assertSee('Inicio')
        ->assertSee('Beta Corp')
        ->assertSee('UM')
        ->click('UM')
        ->assertSee('Perfil')
        ->assertSee('Salir');
});
