<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Policies\CompanyUserPolicy;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    MenuCatalogCache::forget();

    Role::findOrCreate(Role::SUPER_ADMIN);
    Role::findOrCreate(Role::MANAGER);

    $this->company = Company::factory()->create(['status' => CompanyStatus::Active]);
    $this->other = Company::factory()->create(['status' => CompanyStatus::Active]);

    $this->usuarios = app(CreateModule::class)->handle([
        'name' => 'Usuarios',
        'slug' => 'usuarios',
        'label' => ['es' => 'Usuarios'],
        'color' => '#0369a1',
        'icon' => 'users',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->usuarios->id,
        'name' => 'Company users',
        'slug' => 'company-users',
        'label' => ['es' => 'Usuarios empresa'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'usuarios');
    enableModuleForCompany($this->other, 'usuarios');

    app(EnsureCompanyDefaultRoles::class)->handle($this->company);
    MenuCatalogCache::forget();
});

function actingBulkActor(Company $company, bool $withDelete = true, bool $withUpdate = true): User
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

    if ($withDelete) {
        $perms[] = Permission::findOrCreate('company-users.delete');
    }

    $user->givePermissionTo($perms);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('expone rutas bulk-detach y bulk-status', function () {
    expect(Route::has('admin.company-users.bulk-detach'))->toBeTrue()
        ->and(Route::has('admin.company-users.bulk-status'))->toBeTrue();
});

it('bulk detach quita vínculo de sesión y no borra el User', function () {
    actingBulkActor($this->company);

    $member = User::factory()->create(['status' => true]);
    $member->companies()->attach($this->company->id);
    $member->companies()->attach($this->other->id);

    $this->post(route('admin.company-users.bulk-detach'), [
        'ids' => [$member->id],
    ])->assertRedirect();

    expect($member->companies()->where('companies.id', $this->company->id)->exists())->toBeFalse()
        ->and($member->companies()->where('companies.id', $this->other->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($member->id)->exists())->toBeTrue();
});

it('bulk status activa y desactiva users vinculados', function () {
    actingBulkActor($this->company);

    $member = User::factory()->create(['status' => false]);
    $member->companies()->attach($this->company->id);

    $this->post(route('admin.company-users.bulk-status'), [
        'ids' => [$member->id],
        'status' => true,
    ])->assertRedirect();

    expect($member->fresh()->status)->toBeTrue();

    $this->post(route('admin.company-users.bulk-status'), [
        'ids' => [$member->id],
        'status' => false,
    ])->assertRedirect();

    expect($member->fresh()->status)->toBeFalse();
});

it('bulk con ids ajenos a sesión falla Policy y no aplica el lote (R-TEN-05)', function () {
    actingBulkActor($this->company);

    $foreign = User::factory()->create(['status' => true]);
    $foreign->companies()->attach($this->other->id);

    $local = User::factory()->create(['status' => true]);
    $local->companies()->attach($this->company->id);

    // Opción A: authorize por id — un ajeno → 403 de todo el lote (no a medias).
    $this->post(route('admin.company-users.bulk-detach'), [
        'ids' => [$foreign->id, $local->id],
    ])->assertForbidden();

    expect($foreign->companies()->where('companies.id', $this->other->id)->exists())->toBeTrue()
        ->and($local->companies()->where('companies.id', $this->company->id)->exists())->toBeTrue()
        ->and(User::query()->whereKey($foreign->id)->exists())->toBeTrue();

    $foreignB = User::factory()->create(['status' => true]);
    $foreignB->companies()->attach($this->other->id);

    $this->post(route('admin.company-users.bulk-status'), [
        'ids' => [$foreignB->id],
        'status' => false,
    ])->assertForbidden();

    expect($foreignB->fresh()->status)->toBeTrue();
});

it('bulk sin permiso responde 403', function () {
    actingBulkActor($this->company, withDelete: false, withUpdate: false);

    $member = User::factory()->create(['status' => true]);
    $member->companies()->attach($this->company->id);

    $this->post(route('admin.company-users.bulk-detach'), [
        'ids' => [$member->id],
    ])->assertForbidden();

    $this->post(route('admin.company-users.bulk-status'), [
        'ids' => [$member->id],
        'status' => false,
    ])->assertForbidden();
});

it('Policy deniega bulk si el actor no pertenece a CurrentCompany (hueco Spatie-only)', function () {
    // HTTP no alcanza el controller: SetCurrentCompany limpia sesión si el actor
    // no belongsToCompany y EnsureCompanySelected redirige (302). Cubiertos por
    // Policy directa + el 403 HTTP sin company-users.delete|update.
    $actor = User::factory()->create(['status' => true]);
    $actor->companies()->attach($this->other->id);

    setPermissionsTeamId($this->company->id);
    $actor->givePermissionTo([
        Permission::findOrCreate('company-users.update'),
        Permission::findOrCreate('company-users.delete'),
    ]);
    $actor->unsetRelation('roles');
    $actor->unsetRelation('permissions');

    withCurrentCompany($this->company);

    $member = User::factory()->create(['status' => true]);
    $member->companies()->attach($this->company->id);

    $policy = app(CompanyUserPolicy::class);

    expect($policy->delete($actor, $member))->toBeFalse()
        ->and($policy->update($actor, $member))->toBeFalse();
});

it('bulk valida ids requeridos', function () {
    actingBulkActor($this->company);

    $this->post(route('admin.company-users.bulk-detach'), [])
        ->assertSessionHasErrors('ids');

    $this->post(route('admin.company-users.bulk-status'), [
        'status' => true,
    ])->assertSessionHasErrors('ids');
});

it('Index declara massActions y ServerTable soporta el contrato', function () {
    $index = (string) file_get_contents(resource_path('js/Pages/Admin/CompanyUsers/Index.tsx'));
    $table = (string) file_get_contents(resource_path('js/Components/Table/ServerTable.tsx'));
    $frontend = (string) file_get_contents(base_path('docs/standards/frontend.md'));

    expect($index)->toContain('massActions={massActions}')
        ->and($index)->toContain('bulk-detach')
        ->and($index)->toContain('bulk-status')
        ->and($index)->toContain('confirmar_eliminar_masivo')
        ->and($table)->toContain('massActions?: MassAction')
        ->and($table)->toContain("field: '_mass_select'")
        ->and($table)->toContain('acciones_masivas')
        ->and($table)->toContain('selectedCount === 0')
        ->and($frontend)->toContain('Mass actions (opt-in)')
        ->and($frontend)->toContain('preguntar qué acciones');
});
