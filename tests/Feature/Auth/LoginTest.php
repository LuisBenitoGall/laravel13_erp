<?php

use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('siembra Super Admin con company_id NULL y usuario de trabajo', function () {
    $this->seed(AdminCatalogSeeder::class);

    $role = Role::findByName('Super Admin');
    expect($role->getAttribute('company_id'))->toBeNull();

    $user = User::query()->where('email', config('superadmin.email'))->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole('Super Admin'))->toBeTrue();
});

it('login del superadmin redirige al catálogo', function () {
    $this->seed(AdminCatalogSeeder::class);

    $this->post(route('login'), [
        'email' => config('superadmin.email'),
        'password' => config('superadmin.password'),
    ])->assertRedirect(route('admin.modules.index'));

    $this->actingAs(User::query()->where('email', config('superadmin.email'))->first())
        ->get(route('admin.modules.index'))
        ->assertOk();
});
