<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'file']);
});

it('usuario sin empresa vinculada no entra al ERP', function () {
    User::factory()->create([
        'email' => 'no-company@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('email', 'no-company@example.com')
        ->fill('password', 'password')
        ->click('Acceder')
        ->assertPathIs('/login')
        ->assertSee(__('auth.no_company'))
        ->assertSee('Entrar')
        ->assertSee('Acceder')
        ->assertDontSee('Salir')
        ->assertDontSee('Seleccionar empresa');
});
