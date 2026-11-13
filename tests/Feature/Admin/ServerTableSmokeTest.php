<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    $this->seed(AdminCatalogSeeder::class);
});

it('Index Module responde JSON de ServerTable', function () {
    $user = User::query()->where('email', config('superadmin.email'))->first();
    $company = Company::query()->where('slug', 'acme')->firstOrFail();
    actingWithCompany($user, $company);

    $this->getJson(route('admin.modules.table'))
        ->assertOk()
        ->assertJsonStructure(['data', 'last_page', 'total']);

    expect(Module::query()->where('slug', 'configuracion')->exists())->toBeTrue()
        ->and(Module::query()->whereIn('slug', ['admin', 'workspace'])->exists())->toBeFalse();
});
