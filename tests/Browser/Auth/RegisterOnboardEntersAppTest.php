<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'file']);

    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('registro y onboarding de empresa entran al AppLayout usable', function () {
    $page = visit('/register');

    $page->assertSee('Crear cuenta')
        ->fill('name', 'Ada')
        ->fill('surname', 'Onboard')
        ->fill('email', 'ada-onboard@example.com')
        ->fill('password', 'Password1!')
        ->fill('password_confirmation', 'Password1!')
        ->check('terms')
        ->click('Continuar')
        ->assertPathIs('/onboarding/company')
        ->assertSee('Tu empresa')
        ->fill('nif', 'B99887766')
        ->fill('name', 'Empresa Onboard E2E')
        ->fill('tradename', 'Onboard E2E')
        ->click('Crear empresa y entrar')
        ->assertPathIs('/dashboard')
        ->assertSee('Empresa Onboard E2E')
        ->assertSee('AD')
        ->assertSee('Panel principal de la empresa activa.')
        ->click('AD')
        ->assertSee('Perfil')
        ->assertSee('Salir');
});
