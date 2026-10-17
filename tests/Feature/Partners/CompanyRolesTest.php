<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->workspace = Company::factory()->create(['slug' => 'workspace']);
    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($this->workspace, 'admin');

    $this->actor = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->actor->assignRole('Super Admin');
    actingWithCompany($this->actor, $this->workspace);
});

it('al crear company genera manager y guest con company_id y asigna manager al creador', function () {
    $this->post(route('partners.companies.store'), [
        'name' => 'Acme',
        'slug' => 'acme-roles',
        'tradename' => 'Acme',
        'nif' => 'B12345678',
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ])->assertRedirect();

    $company = Company::query()->where('slug', 'acme-roles')->firstOrFail();

    expect($this->actor->companies()->where('companies.id', $company->id)->exists())->toBeTrue();

    setPermissionsTeamId($company->id);
    $manager = Role::findByName('manager');
    $guest = Role::findByName('guest');

    expect((int) $manager->getAttribute('company_id'))->toBe($company->id)
        ->and((int) $guest->getAttribute('company_id'))->toBe($company->id)
        ->and($this->actor->hasRole('manager'))->toBeTrue();

    $guestPermissionCount = DB::table('role_has_permissions')
        ->where('role_id', $guest->id)
        ->count();

    expect($guestPermissionCount)->toBe(0);
});
