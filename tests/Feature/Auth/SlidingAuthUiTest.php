<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('GET login renderiza SlidingAuth en mode login', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Auth/SlidingAuth')
            ->where('mode', 'login'));
});

it('GET register renderiza SlidingAuth en mode register', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Auth/SlidingAuth')
            ->where('mode', 'register'));
});

it('SlidingAuth declara shell testid, reduced-motion y toggle local sin Inertia visit', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Auth/SlidingAuth.tsx'));

    expect($source)->toContain('data-testid="auth-sliding-panel"')
        ->and($source)->toContain('motion-reduce:transition-none')
        ->and($source)->toContain('history.replaceState')
        ->and($source)->not->toContain('router.visit')
        ->and($source)->toContain('inert={isRegister || undefined}')
        ->and($source)->toContain('inert={!isRegister || undefined}')
        ->and($source)->toContain("t('auth_sliding_terminos')")
        ->and($source)->not->toMatch('/<\/Link>\s*\./');
});

it('AuthLayout variant sliding no rompe el default estático', function () {
    $source = (string) file_get_contents(resource_path('js/Layouts/AuthLayout.tsx'));

    expect($source)->toContain("variant?: 'static' | 'sliding'")
        ->and($source)->toContain("variant = 'static'")
        ->and($source)->toContain('--auth-primary');
});
