<?php

use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('siembra Super Admin con company_id NULL y ACME vinculada', function () {
    $this->seed(AdminCatalogSeeder::class);

    $role = Role::findByName('Super Admin');
    expect($role->getAttribute('company_id'))->toBeNull();

    $user = User::query()->where('email', config('superadmin.email'))->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole('Super Admin'))->toBeTrue()
        ->and(Company::query()->where('slug', 'acme')->where('nif', '00000000A')->exists())->toBeTrue()
        ->and($user->companies()->where('companies.slug', 'acme')->exists())->toBeTrue();
});

it('login del superadmin redirige al dashboard', function () {
    $this->seed(AdminCatalogSeeder::class);

    $this->post(route('login'), [
        'email' => config('superadmin.email'),
        'password' => config('superadmin.password'),
    ])->assertRedirect(route('dashboard'));

    $user = User::query()->where('email', config('superadmin.email'))->first();
    $company = $user->companies()->first();

    actingWithCompany($user, $company);

    $this->get(route('admin.modules.index'))->assertOk();
});
