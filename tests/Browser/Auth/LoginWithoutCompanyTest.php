<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'file']);
});

it('usuario sin empresa vinculada no entra al ERP y va a onboarding', function () {
    User::factory()->create([
        'email' => 'no-company@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('email', 'no-company@example.com')
        ->fill('password', 'password')
        ->click('Acceder')
        ->assertPathIs('/onboarding/company')
        ->assertSee('Tu empresa')
        ->assertDontSee('Salir');
});
