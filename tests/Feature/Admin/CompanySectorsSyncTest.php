<?php

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);

    Module::factory()->create([
        'slug' => 'empresas',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
});

/**
 * @param  list<string>  $extraPermissions
 */
function actingCompanySectorEditor(Company $company, array $extraPermissions = [
    'company-sectors.view',
    'company-sectors.update',
]): User
{
    enableModuleForCompany($company, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $names = array_merge(
        ['empresas.access', 'companies.view', 'companies.update'],
        $extraPermissions,
    );
    $user->givePermissionTo(
        collect($names)->map(fn (string $name) => Permission::findOrCreate($name))->all(),
    );
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('sincroniza sectores Active de la company vinculada', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanySectorEditor($company);

    $a = Sector::factory()->create(['status' => SectorStatus::Active]);
    $b = Sector::factory()->create(['status' => SectorStatus::Active]);
    $inactive = Sector::factory()->inactive()->create();

    $this->put(route('admin.companies.sectors.sync', $company), [
        'sector_ids' => [$a->id, $b->id, $inactive->id],
    ])->assertSessionHasErrors('sector_ids.2');

    $this->put(route('admin.companies.sectors.sync', $company), [
        'sector_ids' => [$a->id, $b->id],
    ])->assertRedirect();

    expect($company->fresh()->sectors()->pluck('sectors.id')->sort()->values()->all())
        ->toBe(collect([$a->id, $b->id])->sort()->values()->all());

    $this->put(route('admin.companies.sectors.sync', $company), [
        'sector_ids' => [$a->id],
    ])->assertRedirect();

    expect($company->fresh()->sectors()->pluck('sectors.id')->all())->toBe([$a->id]);

    $this->put(route('admin.companies.sectors.sync', $company), [
        'sector_ids' => [],
    ])->assertRedirect();

    expect($company->fresh()->sectors()->count())->toBe(0);
});

it('Edit expone tab sectores con can; Create no tiene UI de sectores', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanySectorEditor($company);

    $active = Sector::factory()->create([
        'status' => SectorStatus::Active,
        'name' => ['es' => 'Activo', 'en' => 'Active', 'ca' => 'Actiu'],
    ]);
    Sector::factory()->inactive()->create();
    $company->sectors()->attach($active->id);

    $this->get(route('admin.companies.edit', $company))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Companies/Edit')
            ->has('sectors', 1)
            ->where('sectors.0.id', $active->id)
            ->where('can.view_company_sectors', true)
            ->where('can.update_company_sectors', true)
            ->has('sector_options')
            ->where('sector_options', fn ($rows) => collect($rows)->contains(
                fn ($row) => (int) $row['id'] === $active->id,
            ) && collect($rows)->count() === 1));

    $create = (string) file_get_contents(resource_path('js/Pages/Admin/Companies/Create.tsx'));
    $edit = (string) file_get_contents(resource_path('js/Pages/Admin/Companies/Edit.tsx'));

    expect($create)->not->toContain('CompanySectorsPanel')
        ->and($create)->not->toContain('sector_options')
        ->and($edit)->toContain('CompanySectorsPanel')
        ->and($edit)->toContain("'sectors'")
        ->and($edit)->toContain('canViewSectors')
        ->and(substr_count($edit, '<CompanySectorsPanel'))->toBe(1);
});

it('view-only: ve tab pero sync 403; sin view can.view false', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    $sector = Sector::factory()->create(['status' => SectorStatus::Active]);

    actingCompanySectorEditor($company, ['company-sectors.view']);

    $this->get(route('admin.companies.edit', $company))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('can.view_company_sectors', true)
            ->where('can.update_company_sectors', false));

    $this->put(route('admin.companies.sectors.sync', $company), [
        'sector_ids' => [$sector->id],
    ])->assertForbidden();

    actingCompanySectorEditor($company, []);

    $this->get(route('admin.companies.edit', $company))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('can.view_company_sectors', false)
            ->where('can.update_company_sectors', false));

    // companies.update solo no basta para sync (hace falta company-sectors.update).
    $this->put(route('admin.companies.sectors.sync', $company), [
        'sector_ids' => [$sector->id],
    ])->assertForbidden();
});

it('rechaza sync de company no vinculada', function () {
    $linked = Company::factory()->create(['status' => CompanyStatus::Active]);
    $foreign = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanySectorEditor($linked);

    $sector = Sector::factory()->create(['status' => SectorStatus::Active]);

    $this->put(route('admin.companies.sectors.sync', $foreign), [
        'sector_ids' => [$sector->id],
    ])->assertForbidden();
});

it('Edit omite pivotes Inactive y sync los limpia al guardar', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanySectorEditor($company);

    $stillActive = Sector::factory()->create(['status' => SectorStatus::Active]);
    $becameInactive = Sector::factory()->create(['status' => SectorStatus::Active]);
    $company->sectors()->attach([$stillActive->id, $becameInactive->id]);

    $becameInactive->update(['status' => SectorStatus::Inactive]);

    expect($company->fresh()->sectors()->pluck('sectors.id')->sort()->values()->all())
        ->toBe(collect([$stillActive->id, $becameInactive->id])->sort()->values()->all());

    $this->get(route('admin.companies.edit', $company))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Companies/Edit')
            ->has('sectors', 1)
            ->where('sectors.0.id', $stillActive->id)
            ->where('sector_options', fn ($rows) => collect($rows)->contains(
                fn ($row) => (int) $row['id'] === $stillActive->id,
            ) && collect($rows)->every(
                fn ($row) => (int) $row['id'] !== $becameInactive->id,
            )));

    $this->put(route('admin.companies.sectors.sync', $company), [
        'sector_ids' => [$becameInactive->id],
    ])->assertSessionHasErrors('sector_ids.0');

    $this->put(route('admin.companies.sectors.sync', $company), [
        'sector_ids' => [$stillActive->id],
    ])->assertRedirect();

    expect($company->fresh()->sectors()->pluck('sectors.id')->all())
        ->toBe([$stillActive->id])
        ->and($company->fresh()->sectors()->whereKey($becameInactive->id)->exists())->toBeFalse();
});
