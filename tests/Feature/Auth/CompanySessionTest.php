<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
});

it('login sin empresa vinculada redirige a onboarding con sesión', function () {
    $user = User::factory()->create([
        'email' => 'lonely@example.com',
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('onboarding.company'));

    $this->assertAuthenticatedAs($user);
});

it('login con una empresa fija sesión automáticamente', function () {
    $company = Company::factory()->create();
    enableModuleForCompany($company, 'admin');

    $user = User::factory()->create([
        'email' => 'one@example.com',
        'password' => 'password',
    ]);
    $user->companies()->attach($company->id);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    expect(session('current_company_id'))->toBe($company->id);
});

it('login con N empresas redirige a selección bloqueante', function () {
    $a = Company::factory()->create(['slug' => 'a']);
    $b = Company::factory()->create(['slug' => 'b']);

    $user = User::factory()->create([
        'email' => 'many@example.com',
        'password' => 'password',
    ]);
    $user->companies()->attach([$a->id, $b->id]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('companies.select'));

    expect(session('current_company_id'))->toBeNull();
});

it('rechaza fijar empresa ajena', function () {
    $own = Company::factory()->create(['slug' => 'own']);
    $other = Company::factory()->create(['slug' => 'other']);
    enableModuleForCompany($own, 'admin');

    $user = User::factory()->create();
    actingWithCompany($user, $own);

    $this->post(route('companies.switch'), [
        'company_id' => $other->id,
    ])->assertSessionHasErrors('company_id');

    expect(session('current_company_id'))->toBe($own->id)
        ->and(app(CurrentCompany::class)->id())->toBe($own->id);
});

it('Super Admin no bypasa pertenencia de empresa', function () {
    $own = Company::factory()->create(['slug' => 'sa-own']);
    $other = Company::factory()->create(['slug' => 'sa-other']);
    enableModuleForCompany($own, 'admin');

    $user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $user->assignRole('Super Admin');
    actingWithCompany($user, $own);

    $this->post(route('companies.select.store'), [
        'company_id' => $other->id,
    ])->assertSessionHasErrors('company_id');
});
