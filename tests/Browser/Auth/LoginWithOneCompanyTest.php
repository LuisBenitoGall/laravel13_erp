<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
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

    $page->fill('#d-login-email', 'one-company@example.com')
        ->fill('#d-login-password', 'password')
        ->click('form:has(#d-login-email) button[type="submit"]')
        ->assertPathIs('/dashboard')
        ->assertSee('Mis opciones preferidas')
        ->assertSee('Dashboard')
        ->assertSee('Empresa Una')
        ->assertSee('UU')
        ->click('UU')
        ->assertSee('Perfil')
        ->assertSee('Salir');
});
