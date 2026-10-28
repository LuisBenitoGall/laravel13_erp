<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    Role::findOrCreate(Role::SUPER_ADMIN);

    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);

    $this->company = Company::factory()->create([
        'status' => CompanyStatus::Active,
    ]);

    $this->user = User::factory()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);
    $this->user->assignRole(Role::SUPER_ADMIN);
});

it('perfil requiere autenticación', function () {
    $this->get(route('profile.show'))->assertRedirect(route('login'));
});

it('usuario con company ve perfil placeholder', function () {
    actingWithCompany($this->user, $this->company);

    $this->get(route('profile.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Profile/Show')
            ->where('profile.name', 'Ada Lovelace')
            ->where('profile.email', 'ada@example.com'));
});

it('dashboard con AppLayout no incluye title en shared props de chrome', function () {
    actingWithCompany($this->user, $this->company);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('auth.user')
            ->has('available_companies')
            ->has('tenant'));
});

it('logout cierra sesión', function () {
    actingWithCompany($this->user, $this->company);

    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest();
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
