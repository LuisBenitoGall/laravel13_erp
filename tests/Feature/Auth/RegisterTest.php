<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registra usuario, inicia sesión y redirige a onboarding', function () {
    $this->post(route('register'), [
        'name' => 'Ada',
        'surname' => 'Lovelace',
        'email' => 'ada@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'terms' => '1',
    ])->assertRedirect(route('onboarding.company'));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'ada@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Ada')
        ->and($user->surname)->toBe('Lovelace');
});

it('falla sin apellidos', function () {
    $this->post(route('register'), [
        'name' => 'Ada',
        'email' => 'ada-nosurname@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'terms' => '1',
    ])->assertSessionHasErrors('surname');

    $this->assertGuest();
    expect(User::query()->where('email', 'ada-nosurname@example.com')->exists())->toBeFalse();
});

it('falla sin aceptación de términos', function () {
    $this->post(route('register'), [
        'name' => 'Ada',
        'surname' => 'Lovelace',
        'email' => 'ada2@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertSessionHasErrors('terms');

    $this->assertGuest();
});

it('falla con email duplicado', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $this->post(route('register'), [
        'name' => 'Other',
        'surname' => 'User',
        'email' => 'dup@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'terms' => '1',
    ])->assertSessionHasErrors('email');
});

it('falla si name supera max 255', function () {
    $this->post(route('register'), [
        'name' => str_repeat('a', 256),
        'surname' => 'Lovelace',
        'email' => 'ada-toolong@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'terms' => '1',
    ])->assertSessionHasErrors('name');

    $this->assertGuest();
    expect(User::query()->where('email', 'ada-toolong@example.com')->exists())->toBeFalse();
});

it('muestra páginas legales placeholder', function () {
    $this->get(route('legal.notice'))->assertOk()->assertInertia(fn ($page) => $page->component('Legal/Notice'));
    $this->get(route('legal.terms'))->assertOk()->assertInertia(fn ($page) => $page->component('Legal/Terms'));
});
