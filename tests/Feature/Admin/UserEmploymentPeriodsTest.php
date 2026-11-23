<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\ContractType;
use App\Domains\HR\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    MenuCatalogCache::forget();

    Role::findOrCreate(Role::SUPER_ADMIN);
    Role::findOrCreate(Role::MANAGER);

    $this->companyA = Company::factory()->create([
        'name' => 'Alpha Co',
        'status' => CompanyStatus::Active,
    ]);
    $this->companyB = Company::factory()->create([
        'name' => 'Beta Co',
        'status' => CompanyStatus::Active,
    ]);

    $usuarios = app(CreateModule::class)->handle([
        'name' => 'Usuarios',
        'slug' => 'usuarios',
        'label' => ['es' => 'Usuarios'],
        'color' => '#0369a1',
        'icon' => 'users',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $usuarios->id,
        'name' => 'Company users',
        'slug' => 'company-users',
        'label' => ['es' => 'Usuarios empresa'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->companyA, 'usuarios');
    enableModuleForCompany($this->companyB, 'usuarios');
    MenuCatalogCache::forget();
});

function actingEmploymentEditor(Company $company, bool $withUpdate = true): User
{
    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $perms = [
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-users.view'),
        Permission::findOrCreate('company-users.viewAny'),
    ];
    if ($withUpdate) {
        $perms[] = Permission::findOrCreate('company-users.update');
    }
    $user->givePermissionTo($perms);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

function seedPeriod(Company $company, User $member, array $overrides = []): Employee
{
    $type = ContractType::factory()->create(['company_id' => $company->id]);
    $workplace = Workplace::factory()->create(['company_id' => $company->id]);

    return Employee::factory()->create(array_merge([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'workplace_id' => $workplace->id,
        'job_title' => 'Dev',
        'start_date' => '2026-01-01',
        'end_date' => null,
        'weekly_hours' => 40,
        'year_salary' => '30000.00',
        'status' => EmployeeStatus::Active,
        'observations' => null,
    ], $overrides));
}

it('has_employment_periods false sin periodos y true con periodos (profile y company-users)', function () {
    $actor = actingEmploymentEditor($this->companyA);
    $member = User::factory()->create(['status' => true]);
    $this->companyA->users()->attach($member->id);

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('has_employment_periods', false));

    $this->get(route('admin.company-users.edit', $member))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('has_employment_periods', false));

    seedPeriod($this->companyA, $actor, ['observations' => 'Nota A']);
    seedPeriod($this->companyB, $member);

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('has_employment_periods', true));

    $this->get(route('admin.company-users.edit', $member))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('has_employment_periods', true));
});

it('table profile lista periodos multi-company del sujeto con observations', function () {
    $actor = actingEmploymentEditor($this->companyA);
    $actor->companies()->attach($this->companyB->id);

    $inA = seedPeriod($this->companyA, $actor, [
        'job_title' => 'Alpha role',
        'observations' => 'Obs Alpha',
    ]);
    $inB = seedPeriod($this->companyB, $actor, [
        'job_title' => 'Beta role',
        'observations' => null,
        'start_date' => '2025-06-01',
    ]);

    // Otro user: no debe aparecer.
    $other = User::factory()->create(['status' => true]);
    $this->companyA->users()->attach($other->id);
    seedPeriod($this->companyA, $other, ['job_title' => 'Other']);

    $response = $this->getJson(route('admin.profile.employment-periods.table'))
        ->assertOk()
        ->assertJsonPath('total', 2);

    $rows = collect($response->json('data'));
    expect($rows->pluck('id')->all())->toContain($inA->id, $inB->id)
        ->and($rows->pluck('company_name')->all())->toContain('Alpha Co', 'Beta Co')
        ->and($rows->firstWhere('id', $inA->id)['observations'])->toBe('Obs Alpha')
        ->and($rows->firstWhere('id', $inB->id)['observations'])->toBeNull()
        ->and($rows->firstWhere('id', $inA->id))->not->toHaveKey('status')
        ->and($rows->firstWhere('id', $inA->id))->not->toHaveKey('can');
});

it('table company-users multi-company sin exigir employees.view; 403 sin acceso ficha', function () {
    $editor = actingEmploymentEditor($this->companyA);
    $member = User::factory()->create(['status' => true]);
    $member->companies()->attach([$this->companyA->id, $this->companyB->id]);

    seedPeriod($this->companyA, $member, ['job_title' => 'A']);
    seedPeriod($this->companyB, $member, ['job_title' => 'B', 'start_date' => '2024-01-01']);

    expect($editor->can('employees.view'))->toBeFalse();

    $this->getJson(route('admin.company-users.employment-periods.table', $member))
        ->assertOk()
        ->assertJsonPath('total', 2);

    $outsider = actingEmploymentEditor($this->companyB);
    $foreign = User::factory()->create(['status' => true]);
    $this->companyA->users()->attach($foreign->id);
    seedPeriod($this->companyA, $foreign);

    // Editor en B sin vínculo del sujeto a B → 403.
    $this->getJson(route('admin.company-users.employment-periods.table', $foreign))
        ->assertForbidden();

    // Sin permiso updateCompanyUser.
    $viewer = actingEmploymentEditor($this->companyA, withUpdate: false);
    $this->getJson(route('admin.company-users.employment-periods.table', $member))
        ->assertForbidden();
});
