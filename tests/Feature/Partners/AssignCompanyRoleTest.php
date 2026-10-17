<?php

use App\Domains\Admin\Actions\AssignCompanyRole;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->company = Company::factory()->create();
    app(EnsureCompanyDefaultRoles::class)->handle($this->company);

    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($this->company, 'admin');

    $this->manager = User::factory()->create();
    $this->manager->companies()->attach($this->company->id);
    app(AssignCompanyRole::class)->handle($this->manager, $this->company, 'manager');

    $this->member = User::factory()->create();
    $this->member->companies()->attach($this->company->id);
});

it('asigna guest a usuario vinculado sin crear roles nuevos', function () {
    actingWithCompany($this->manager, $this->company);

    $rolesBefore = Role::query()->where('company_id', $this->company->id)->count();

    $this->put(route('partners.companies.members.role', [$this->company, $this->member]), [
        'role' => 'guest',
    ])->assertRedirect();

    setPermissionsTeamId($this->company->id);
    expect($this->member->fresh()->hasRole('guest'))->toBeTrue()
        ->and(Role::query()->where('company_id', $this->company->id)->count())->toBe($rolesBefore);
});

it('manager puede promover a manager', function () {
    actingWithCompany($this->manager, $this->company);

    $this->put(route('partners.companies.members.role', [$this->company, $this->member]), [
        'role' => 'manager',
    ])->assertRedirect();

    setPermissionsTeamId($this->company->id);
    expect($this->member->fresh()->hasRole('manager'))->toBeTrue();
});

it('usuario sin rol manager no puede asignar', function () {
    $outsider = User::factory()->create();
    actingWithCompany($outsider, $this->company);

    $this->put(route('partners.companies.members.role', [$this->company, $this->member]), [
        'role' => 'guest',
    ])->assertForbidden();
});
