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

    Role::findOrCreate(Role::SUPER_ADMIN);
    foreach ([Role::MANAGER, Role::GUEST] as $name) {
        $tpl = Role::findOrCreate($name);
        $tpl->forceFill(['universal' => true])->save();
    }

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

function localCustomRole(Company $company, string $name = 'vendedor'): Role
{
    $previous = getPermissionsTeamId();
    setPermissionsTeamId($company->id);

    try {
        return Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'universal' => false,
            'description' => 'Custom sales',
        ]);
    } finally {
        setPermissionsTeamId($previous);
    }
}

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

it('asigna rol custom a miembro vinculado', function () {
    localCustomRole($this->company);
    actingWithCompany($this->manager, $this->company);

    $this->put(route('partners.companies.members.role', [$this->company, $this->member]), [
        'role' => 'vendedor',
    ])->assertRedirect();

    setPermissionsTeamId($this->company->id);
    $member = $this->member->fresh();
    $member->unsetRelation('roles');
    expect($member->hasRole('vendedor'))->toBeTrue()
        ->and($member->hasRole('manager'))->toBeFalse()
        ->and($member->hasRole('guest'))->toBeFalse();
});

it('reasignar reemplaza el rol local previo (un solo rol de company)', function () {
    localCustomRole($this->company, 'operador');
    actingWithCompany($this->manager, $this->company);

    app(AssignCompanyRole::class)->handle($this->member, $this->company, 'guest');

    $this->put(route('partners.companies.members.role', [$this->company, $this->member]), [
        'role' => 'operador',
    ])->assertRedirect();

    setPermissionsTeamId($this->company->id);
    $member = $this->member->fresh();
    $member->unsetRelation('roles');
    expect($member->roles->pluck('name')->all())->toBe(['operador']);
});

it('rechaza rol de plataforma o de otra company', function () {
    actingWithCompany($this->manager, $this->company);

    $this->put(route('partners.companies.members.role', [$this->company, $this->member]), [
        'role' => 'Super Admin',
    ])->assertSessionHasErrors('role');

    $other = Company::factory()->create();
    app(EnsureCompanyDefaultRoles::class)->handle($other);
    localCustomRole($other, 'ajeno');

    $this->put(route('partners.companies.members.role', [$this->company, $this->member]), [
        'role' => 'ajeno',
    ])->assertSessionHasErrors('role');

    expect(fn () => app(AssignCompanyRole::class)->handle($this->member, $this->company, 'ajeno'))
        ->toThrow(InvalidArgumentException::class);
});

it('rechaza asignar a usuario no vinculado', function () {
    $outsider = User::factory()->create();

    expect(fn () => app(AssignCompanyRole::class)->handle($outsider, $this->company, 'guest'))
        ->toThrow(InvalidArgumentException::class);
});

it('usuario sin rol manager no puede asignar', function () {
    $outsider = User::factory()->create();
    actingWithCompany($outsider, $this->company);

    $this->put(route('partners.companies.members.role', [$this->company, $this->member]), [
        'role' => 'guest',
    ])->assertForbidden();
});

it('index expone roles locales y rol actual custom', function () {
    localCustomRole($this->company, 'vendedor');
    app(AssignCompanyRole::class)->handle($this->member, $this->company, 'vendedor');
    actingWithCompany($this->manager, $this->company);

    $this->get(route('partners.companies.members.index', $this->company))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Partners/Companies/Members')
            ->has('roles')
            ->where('roles', function ($roles) {
                $names = collect($roles)->pluck('name')->all();

                return in_array('manager', $names, true)
                    && in_array('guest', $names, true)
                    && in_array('vendedor', $names, true);
            })
            ->where('members', function ($members) {
                $row = collect($members)->firstWhere('id', $this->member->id);

                return $row !== null && $row['role'] === 'vendedor';
            }));
});
