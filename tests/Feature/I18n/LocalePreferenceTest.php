<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

it('usa castellano por defecto sin preferencia', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'es')
            ->has('translations')
            ->has('supportedLocales'));

    expect(App::getLocale())->toBe('es');
});

it('permite a un guest cambiar idioma vía sesión', function () {
    $this->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect();

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locale', 'en'));

    expect(session('locale'))->toBe('en');
});

it('persiste el idioma en el usuario autenticado', function () {
    $user = User::factory()->create(['locale' => null]);

    $this->actingAs($user)
        ->post(route('locale.update'), ['locale' => 'ca'])
        ->assertRedirect();

    expect($user->fresh()->locale)->toBe('ca');
    expect(session('locale'))->toBe('ca');

    $this->actingAs($user->fresh())
        ->get(route('onboarding.company'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locale', 'ca'));
});

it('rechaza un locale no soportado', function () {
    $this->post(route('locale.update'), ['locale' => 'fr'])
        ->assertSessionHasErrors('locale');
});
