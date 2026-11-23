<?php

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'file']);

    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('registro y onboarding de empresa entran al AppLayout usable', function () {
    Account::factory()->default()->create(['status' => AccountStatus::Active]);

    $page = visit('/register');

    $page->assertSee('Crear cuenta')
        ->fill('#d-register-name', 'Ada')
        ->fill('#d-register-surname', 'Onboard')
        ->fill('#d-register-email', 'ada-onboard@example.com')
        ->fill('#d-register-password', 'Password1!')
        ->fill('#d-register-password_confirmation', 'Password1!')
        ->check('#d-terms')
        ->click('form:has(#d-register-email) button[type="submit"]')
        ->assertPathIs('/onboarding/company')
        ->assertSee('Tu empresa')
        ->fill('nif', 'B99887766')
        ->fill('name', 'Empresa Onboard E2E')
        ->fill('tradename', 'Onboard E2E')
        ->click('Crear empresa y entrar')
        ->assertPathIs('/dashboard')
        ->assertSee('Completar configuración de la empresa')
        ->assertSee('Empresa Onboard E2E')
        ->assertSee('Mis opciones preferidas');
});
