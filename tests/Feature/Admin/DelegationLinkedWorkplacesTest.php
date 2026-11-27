<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Workplace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);

    if (! Module::query()->where('slug', 'empresas')->exists()) {
        Module::factory()->create([
            'slug' => 'empresas',
            'level' => ModuleLevel::Basic,
            'status' => ModuleStatus::Active,
        ]);
    }
});

/**
 * @param  list<string>  $abilities
 */
function actingLinkedWorkplacesUser(Company $session, array $abilities = ['view', 'viewAny', 'update']): User
{
    enableModuleForCompany($session, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $session);

    setPermissionsTeamId($session->id);
    $names = ['companies.access'];
    foreach ($abilities as $ability) {
        $names[] = "delegations.{$ability}";
    }
    $user->givePermissionTo(collect($names)->map(fn (string $name) => Permission::findOrCreate($name))->all());
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

/** @return array{0: Company, 1: Company, 2: Workplace, 3: Workplace} */
function matrixDelegationWithTwoSeats(): array
{
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create(['parent_id' => $matrix->id]);
    $w1 = Workplace::factory()->create(['company_id' => $matrix->id, 'name' => 'Sede Uno']);
    $w2 = Workplace::factory()->create(['company_id' => $matrix->id, 'name' => 'Sede Dos']);
    $w1->delegations()->attach($delegation->id, ['is_preferred' => true, 'is_preferred_seat' => false]);
    $w2->delegations()->attach($delegation->id, ['is_preferred' => false, 'is_preferred_seat' => false]);

    return [$matrix, $delegation, $w1, $w2];
}

it('table de sedes vinculadas expone columnas geo y sede preferida sin is_preferred', function () {
    [$matrix, $delegation, $w1] = matrixDelegationWithTwoSeats();
    actingLinkedWorkplacesUser($matrix);

    $response = $this->getJson(route('admin.delegations.workplaces.table', $delegation))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'cp',
                    'town_name',
                    'province_name',
                    'country_name',
                    'created_at',
                    'is_preferred_seat',
                ],
            ],
            'last_page',
            'total',
        ]);

    $row = collect($response->json('data'))->firstWhere('id', $w1->id);
    expect($row)->not->toBeNull()
        ->and($row)->not->toHaveKey('is_preferred')
        ->and($row['is_preferred_seat'])->toBeFalse();
});

it('UI ServerTable filtra geo/fecha y Editar vuelve a la Delegación', function () {
    $panel = file_get_contents(
        resource_path('js/Pages/Admin/Delegations/components/DelegationLinkedWorkplacesTable.tsx'),
    );
    $edit = file_get_contents(resource_path('js/Pages/Admin/Workplaces/Edit.tsx'));

    expect($panel)->toContain("field: 'town_name'")
        ->and($panel)->toContain("field: 'province_name'")
        ->and($panel)->toContain("field: 'country_name'")
        ->and($panel)->toContain("field: 'created_at'")
        ->and($panel)->toContain("headerFilterKind: 'string'")
        ->and($panel)->toContain("headerFilterKind: 'date'")
        ->and($panel)->toContain("labelKey: 'sede_preferida'")
        ->and($panel)->toContain('return_to=')
        ->and($panel)->toContain('tab=workplaces')
        ->and($edit)->toContain('return_to');
});

it('marca y limpia sede preferida con como máximo una por Delegación', function () {
    [$matrix, $delegation, $w1, $w2] = matrixDelegationWithTwoSeats();
    actingLinkedWorkplacesUser($matrix);

    $this->putJson(route('admin.delegations.preferred-workplace', $delegation), [
        'workplace_id' => $w1->id,
    ])->assertOk()
        ->assertJson(['workplace_id' => $w1->id, 'is_preferred_seat' => true]);

    expect(
        (bool) DB::table('workplace_delegation')
            ->where('company_id', $delegation->id)
            ->where('workplace_id', $w1->id)
            ->value('is_preferred_seat'),
    )->toBeTrue()
        ->and(
            (bool) DB::table('workplace_delegation')
                ->where('company_id', $delegation->id)
                ->where('workplace_id', $w2->id)
                ->value('is_preferred_seat'),
        )->toBeFalse();

    $this->putJson(route('admin.delegations.preferred-workplace', $delegation), [
        'workplace_id' => $w2->id,
    ])->assertOk();

    expect(
        DB::table('workplace_delegation')
            ->where('company_id', $delegation->id)
            ->where('is_preferred_seat', true)
            ->count(),
    )->toBe(1)
        ->and(
            (int) DB::table('workplace_delegation')
                ->where('company_id', $delegation->id)
                ->where('is_preferred_seat', true)
                ->value('workplace_id'),
        )->toBe($w2->id);

    $this->putJson(route('admin.delegations.preferred-workplace', $delegation), [
        'workplace_id' => null,
    ])->assertOk()
        ->assertJson(['workplace_id' => null, 'is_preferred_seat' => false]);

    expect(
        DB::table('workplace_delegation')
            ->where('company_id', $delegation->id)
            ->where('is_preferred_seat', true)
            ->count(),
    )->toBe(0);
});

it('set seat no altera is_preferred del mapa por sede', function () {
    [$matrix, $delegation, $w1, $w2] = matrixDelegationWithTwoSeats();
    actingLinkedWorkplacesUser($matrix);

    $this->putJson(route('admin.delegations.preferred-workplace', $delegation), [
        'workplace_id' => $w2->id,
    ])->assertOk();

    expect(
        (bool) DB::table('workplace_delegation')
            ->where('company_id', $delegation->id)
            ->where('workplace_id', $w1->id)
            ->value('is_preferred'),
    )->toBeTrue()
        ->and(
            (bool) DB::table('workplace_delegation')
                ->where('company_id', $delegation->id)
                ->where('workplace_id', $w2->id)
                ->value('is_preferred'),
        )->toBeFalse();
});

it('rechaza sede no vinculada a la Delegación', function () {
    [$matrix, $delegation] = matrixDelegationWithTwoSeats();
    $unlinked = Workplace::factory()->create(['company_id' => $matrix->id]);
    actingLinkedWorkplacesUser($matrix);

    $this->from(route('admin.delegations.edit', $delegation))
        ->put(route('admin.delegations.preferred-workplace', $delegation), [
            'workplace_id' => $unlinked->id,
        ])
        ->assertSessionHasErrors('workplace_id');
});

it('perímetro: otra matriz no lee ni marca sedes de la Delegación ajena', function () {
    [$matrix, $delegation, $w1] = matrixDelegationWithTwoSeats();
    $otherMatrix = Company::factory()->create();
    actingLinkedWorkplacesUser($otherMatrix);

    $this->getJson(route('admin.delegations.workplaces.table', $delegation))
        ->assertNotFound();

    $this->putJson(route('admin.delegations.preferred-workplace', $delegation), [
        'workplace_id' => $w1->id,
    ])->assertNotFound();
});

it('SyncWorkplaceDelegations preserva is_preferred_seat al re-sync', function () {
    [$matrix, $delegation, $w1, $w2] = matrixDelegationWithTwoSeats();
    $user = actingLinkedWorkplacesUser($matrix, ['view', 'viewAny', 'update']);

    setPermissionsTeamId($matrix->id);
    $user->givePermissionTo(
        collect(['workplaces.view', 'workplaces.update'])
            ->map(fn (string $name) => Permission::findOrCreate($name))
            ->all(),
    );

    $this->putJson(route('admin.delegations.preferred-workplace', $delegation), [
        'workplace_id' => $w1->id,
    ])->assertOk();

    $this->put(route('admin.workplaces.delegations.sync', $w1), [
        'delegation_ids' => [$delegation->id],
        'preferred_delegation_id' => $delegation->id,
    ])->assertRedirect();

    expect(
        (bool) DB::table('workplace_delegation')
            ->where('company_id', $delegation->id)
            ->where('workplace_id', $w1->id)
            ->value('is_preferred_seat'),
    )->toBeTrue()
        ->and(
            (bool) DB::table('workplace_delegation')
                ->where('company_id', $delegation->id)
                ->where('workplace_id', $w1->id)
                ->value('is_preferred'),
        )->toBeTrue();

    // w2 sigue vinculada desde el factory helper; seat de w1 intacto.
    expect(
        (bool) DB::table('workplace_delegation')
            ->where('company_id', $delegation->id)
            ->where('workplace_id', $w2->id)
            ->value('is_preferred_seat'),
    )->toBeFalse();
});
