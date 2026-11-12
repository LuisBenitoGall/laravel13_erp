<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
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

function actingCompaniesManager(Company $company, bool $withUpdate = true): User
{
    enableModuleForCompany($company, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $perms = [
        Permission::findOrCreate('empresas.access'),
        Permission::findOrCreate('companies.view'),
        Permission::findOrCreate('companies.viewAny'),
    ];
    if ($withUpdate) {
        $perms[] = Permission::findOrCreate('companies.update');
        $perms[] = Permission::findOrCreate('companies.delete');
    }
    $user->givePermissionTo($perms);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('table expone created_at y whitelist de columnas canónicas', function () {
    $company = Company::factory()->create([
        'name' => 'Alpha Co',
        'tradename' => 'Alpha',
        'nif' => 'B11111111',
        'status' => CompanyStatus::Active,
    ]);
    actingCompaniesManager($company);

    $this->getJson(route('partners.companies.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonFragment([
            'name' => 'Alpha Co',
            'tradename' => 'Alpha',
            'nif' => 'B11111111',
            'status' => CompanyStatus::Active->value,
        ])
        ->assertJsonStructure([
            'data' => [
                ['id', 'name', 'tradename', 'nif', 'status', 'created_at'],
            ],
        ]);
});

it('table solo lista companies del forUser del actor', function () {
    $linked = Company::factory()->create(['name' => 'Linked Co', 'status' => CompanyStatus::Active]);
    $foreign = Company::factory()->create(['name' => 'Foreign Co', 'status' => CompanyStatus::Active]);
    $actor = actingCompaniesManager($linked);

    $sa = User::factory()->create();
    actingWithCompany($sa, $linked);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $response = $this->getJson(route('partners.companies.table'));
    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();
    expect($ids)->toContain($linked->id)
        ->and($ids)->not->toContain($foreign->id)
        ->and($actor->companies()->whereKey($foreign->id)->exists())->toBeFalse();
});

it('toggle status draft a active y active a closed; closed a active rechaza', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active]);
    $target = Company::factory()->create(['status' => CompanyStatus::Draft]);
    $actor = actingCompaniesManager($session);
    $actor->companies()->attach($target->id);

    $this->patchJson(route('partners.companies.status', $target), [
        'status' => CompanyStatus::Active->value,
    ])
        ->assertOk()
        ->assertJson(['status' => CompanyStatus::Active->value]);

    expect($target->fresh()->status)->toBe(CompanyStatus::Active);

    $this->patchJson(route('partners.companies.status', $target), [
        'status' => CompanyStatus::Closed->value,
    ])
        ->assertOk()
        ->assertJson(['status' => CompanyStatus::Closed->value]);

    expect($target->fresh()->status)->toBe(CompanyStatus::Closed);

    $this->patchJson(route('partners.companies.status', $target), [
        'status' => CompanyStatus::Active->value,
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('status');

    expect($target->fresh()->status)->toBe(CompanyStatus::Closed);
});

it('status sin update o company no vinculada responde 403', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompaniesManager($company, withUpdate: false);

    $this->patchJson(route('partners.companies.status', $company), [
        'status' => CompanyStatus::Closed->value,
    ])->assertForbidden();

    $linked = Company::factory()->create(['status' => CompanyStatus::Active]);
    $foreign = Company::factory()->create(['status' => CompanyStatus::Draft]);
    $manager = actingCompaniesManager($linked);

    $this->actingAs($manager);
    session(['current_company_id' => $linked->id]);
    withCurrentCompany($linked);

    $this->patchJson(route('partners.companies.status', $foreign), [
        'status' => CompanyStatus::Active->value,
    ])->assertForbidden();
});

it('Index Mis Empresas usa ServerTable canónico con Acciones', function () {
    $index = file_get_contents(resource_path('js/Pages/Partners/Companies/Index.tsx'));

    expect($index)->toContain('ColumnDef')
        ->and($index)->toContain('persistKey="partners.companies"')
        ->and($index)->toContain('exportable')
        ->and($index)->toContain('rowActions')
        ->and($index)->toContain("valueKind: 'date'")
        ->and($index)->toContain("headerFilterKind: 'date'")
        ->and($index)->toContain('fecha_alta')
        ->and($index)->toContain("optionKey: 'mis_empresas'")
        ->and($index)->toContain("kind: 'toggle'")
        ->and($index)->toContain('autorizaciones')
        ->and($index)->toContain('company-module-authorizations')
        ->and($index)->not->toContain('ColumnDefinition')
        ->and($index)->not->toContain("field: 'slug'")
        ->and($index)->not->toContain("field: 'status'")
        ->and($index)->not->toContain('text-teal-700 underline');
});
