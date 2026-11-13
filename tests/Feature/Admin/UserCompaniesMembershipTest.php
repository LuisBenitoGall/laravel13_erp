<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Models\User;
use App\Models\UserCompany;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    MenuCatalogCache::forget();

    Role::findOrCreate(Role::SUPER_ADMIN);
    Role::findOrCreate(Role::MANAGER);

    $this->company = Company::factory()->create([
        'name' => 'Alpha Co',
        'status' => CompanyStatus::Active,
    ]);
    $this->other = Company::factory()->create([
        'name' => 'Beta Co',
        'status' => CompanyStatus::Active,
    ]);
    $this->foreign = Company::factory()->create([
        'name' => 'Gamma Co',
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

    enableModuleForCompany($this->company, 'usuarios');
    enableModuleForCompany($this->other, 'usuarios');
    enableModuleForCompany($this->foreign, 'usuarios');
    MenuCatalogCache::forget();
});

function actingMembershipManager(Company $company, bool $withUpdate = true): User
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

it('pivot acepta position y department nullable', function () {
    expect(Schema::hasColumns('user_companies', ['position', 'department']))->toBeTrue();

    $user = User::factory()->create();
    $membership = UserCompany::factory()->create([
        'user_id' => $user->id,
        'company_id' => $this->company->id,
        'position' => null,
        'department' => null,
    ]);

    expect($membership->fresh()->position)->toBeNull()
        ->and($membership->fresh()->department)->toBeNull();
});

it('expone rutas profile y company-users companies', function () {
    expect(Route::has('admin.profile.companies.table'))->toBeTrue()
        ->and(Route::has('admin.profile.companies.update'))->toBeTrue()
        ->and(Route::has('admin.profile.companies.destroy'))->toBeTrue()
        ->and(Route::has('admin.company-users.companies.table'))->toBeTrue()
        ->and(Route::has('admin.company-users.companies.update'))->toBeTrue()
        ->and(Route::has('admin.company-users.companies.destroy'))->toBeTrue();
});

it('self actualiza position y department del vinculo', function () {
    $actor = actingMembershipManager($this->company);
    $membership = UserCompany::query()
        ->where('user_id', $actor->id)
        ->where('company_id', $this->company->id)
        ->firstOrFail();

    $this->put(route('admin.profile.companies.update', $membership), [
        'position' => 'CTO',
        'department' => 'Tech',
    ])->assertRedirect();

    expect($membership->fresh()->position)->toBe('CTO')
        ->and($membership->fresh()->department)->toBe('Tech')
        ->and($membership->fresh()->company_id)->toBe($this->company->id);

    $this->put(route('admin.profile.companies.update', $membership), [
        'position' => '  ',
        'department' => null,
    ])->assertRedirect();

    expect($membership->fresh()->position)->toBeNull()
        ->and($membership->fresh()->department)->toBeNull();
});

it('search global y filtro de columna encuentran membership por nombre de empresa', function () {
    $actor = actingMembershipManager($this->company);
    $actor->companies()->attach($this->other->id);

    $this->getJson(route('admin.profile.companies.table', [
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Alpha']],
    ]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonFragment(['company_name' => 'Alpha Co']);

    $this->getJson(route('admin.profile.companies.table', [
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Beta']],
    ]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonFragment(['company_name' => 'Beta Co']);

    $this->getJson(route('admin.profile.companies.table', [
        'filter' => [['field' => 'company_name', 'type' => 'like', 'value' => 'Alpha']],
    ]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonFragment(['company_name' => 'Alpha Co']);
});

it('self lista todas las memberships y detacha; ultima company hace logout', function () {
    $actor = actingMembershipManager($this->company);
    $actor->companies()->attach($this->other->id, [
        'position' => 'Dev',
        'department' => 'IT',
    ]);

    $this->getJson(route('admin.profile.companies.table'))
        ->assertOk()
        ->assertJsonPath('total', 2);

    $otherMembership = UserCompany::query()
        ->where('user_id', $actor->id)
        ->where('company_id', $this->other->id)
        ->firstOrFail();

    $this->delete(route('admin.profile.companies.destroy', $otherMembership))
        ->assertRedirect();

    expect($actor->companies()->count())->toBe(1)
        ->and(Company::query()->whereKey($this->other->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($actor->id)->exists())->toBeTrue();

    $last = UserCompany::query()
        ->where('user_id', $actor->id)
        ->where('company_id', $this->company->id)
        ->firstOrFail();

    $this->delete(route('admin.profile.companies.destroy', $last))
        ->assertRedirect(route('login'));

    $this->assertGuest();
    expect($actor->fresh()->companies()->count())->toBe(0);
});

it('self al detach de sesion con otra company cambia current_company_id al menor id', function () {
    $actor = actingMembershipManager($this->company);
    $actor->companies()->attach($this->other->id);

    $ids = [$this->company->id, $this->other->id];
    sort($ids);
    $sessionId = max($ids);
    $remainingId = min($ids);

    session(['current_company_id' => $sessionId]);
    app(CurrentCompany::class)->set($sessionId);

    $sessionMembership = UserCompany::query()
        ->where('user_id', $actor->id)
        ->where('company_id', $sessionId)
        ->firstOrFail();

    $this->delete(route('admin.profile.companies.destroy', $sessionMembership))
        ->assertRedirect();

    expect(session('current_company_id'))->toBe($remainingId)
        ->and($actor->fresh()->companies()->count())->toBe(1);
});

it('company-users: table completo; update ajeno; 403 sin update; SA no bypassa perimetro', function () {
    $manager = actingMembershipManager($this->company);
    $member = User::factory()->create(['email' => 'member-co@example.test']);
    $this->company->users()->attach($member->id, ['position' => 'Ops']);
    $member->companies()->attach($this->other->id);

    $this->getJson(route('admin.company-users.companies.table', $member))
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonFragment(['company_name' => 'Alpha Co', 'position' => 'Ops']);

    $companyMembership = UserCompany::query()
        ->where('user_id', $member->id)
        ->where('company_id', $this->company->id)
        ->firstOrFail();

    $this->put(route('admin.company-users.companies.update', [$member, $companyMembership]), [
        'position' => 'Lead',
        'department' => 'Ops',
    ])->assertRedirect();

    expect($companyMembership->fresh()->position)->toBe('Lead')
        ->and($companyMembership->fresh()->department)->toBe('Ops')
        ->and($companyMembership->fresh()->company_id)->toBe($this->company->id);

    $otherMembership = UserCompany::query()
        ->where('user_id', $member->id)
        ->where('company_id', $this->other->id)
        ->firstOrFail();

    $this->delete(route('admin.company-users.companies.destroy', [$member, $otherMembership]))
        ->assertRedirect();
    expect($member->fresh()->companies()->where('companies.id', $this->other->id)->exists())->toBeFalse()
        ->and($member->fresh()->companies()->where('companies.id', $this->company->id)->exists())->toBeTrue();

    $noUpdate = actingMembershipManager($this->company, withUpdate: false);
    $this->actingAs($noUpdate);
    session(['current_company_id' => $this->company->id]);
    withCurrentCompany($this->company);

    $this->getJson(route('admin.company-users.companies.table', $member))->assertForbidden();
    $this->put(route('admin.company-users.companies.update', [$member, $companyMembership]), [
        'position' => 'Nope',
        'department' => null,
    ])->assertForbidden();
    expect($companyMembership->fresh()->position)->toBe('Lead');

    $sa = User::factory()->create();
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $outsider = User::factory()->create(['email' => 'outsider-co@example.test']);
    $this->foreign->users()->attach($outsider->id);
    $foreignMembership = UserCompany::query()
        ->where('user_id', $outsider->id)
        ->firstOrFail();

    $this->getJson(route('admin.company-users.companies.table', $outsider))->assertForbidden();
    $this->put(route('admin.company-users.companies.update', [$outsider, $foreignMembership]), [
        'position' => 'X',
        'department' => 'Y',
    ])->assertForbidden();
    $this->delete(route('admin.company-users.companies.destroy', [$outsider, $foreignMembership]))
        ->assertForbidden();
    expect($outsider->fresh()->companies()->count())->toBe(1)
        ->and($foreignMembership->fresh()->position)->toBeNull();
});

it('UI tab Empresas usa ServerTable con modal edit sin CTA alta', function () {
    $panel = file_get_contents(resource_path('js/Pages/Users/components/UserCompaniesPanel.tsx'));
    $edit = file_get_contents(resource_path('js/Pages/Users/Edit.tsx'));

    expect($panel)->toContain('ServerTable')
        ->and($panel)->toContain('Dialog')
        ->and($panel)->toContain('Pencil')
        ->and($panel)->toContain('editar_vinculo_empresa')
        ->and($panel)->toContain('eliminar_vinculo')
        ->and($panel)->toContain('updateUrl')
        ->and($panel)->not->toContain('crear_usuario')
        ->and($edit)->toContain('UserCompaniesPanel')
        ->and($edit)->toContain('updateUrl')
        ->and($edit)->toContain("id: 'companies'")
        ->and($edit)->not->toContain('pestana_vacia_proximamente');
});
