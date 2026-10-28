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

it('login con una empresa entra al AppLayout', function () {
    $company = Company::factory()->create([
        'name' => 'Empresa Una',
        'tradename' => 'Empresa Una',
        'slug' => 'empresa-una',
    ]);

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
        ->assertPathIs('/dashboard')
        ->assertSee('Inicio')
        ->assertSee('Dashboard')
        ->assertSee('Empresa Una')
        ->assertSee('UU')
        ->click('UU')
        ->assertSee('Perfil')
        ->assertSee('Salir');
});
