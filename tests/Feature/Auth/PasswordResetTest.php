<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('envía enlace de reset', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'reset@example.com']);

    $this->post(route('password.email'), [
        'email' => $user->email,
    ])->assertRedirect();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('completa el reset con token válido', function () {
    $user = User::factory()->create(['email' => 'reset2@example.com']);
    $token = Password::broker()->createToken($user);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPassword1!',
        'password_confirmation' => 'NewPassword1!',
    ])->assertRedirect(route('login'));

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'NewPassword1!',
    ])->assertRedirect();

    $this->assertAuthenticated();
});

it('muestra páginas forgot y reset', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/ForgotPassword'));

    $this->get(route('password.reset', ['token' => 'test-token', 'email' => 'a@b.c']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/ResetPassword'));
});
