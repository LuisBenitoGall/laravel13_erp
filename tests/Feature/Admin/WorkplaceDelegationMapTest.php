<?php

use App\Domains\Admin\Actions\SyncWorkplaceDelegations;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Workplace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);
});

/**
 * Actor con sesión en `$session` y permisos de workplaces (sin delegations.*:
 * el mapa se gestiona solo con `workplaces.update`).
 *
 * @param  list<string>  $abilities
 */
function actingMapUser(Company $session, array $abilities = ['view', 'viewAny', 'create', 'update', 'delete']): User
{
    if (! Module::query()->where('slug', 'empresas')->exists()) {
        Module::factory()->create([
            'slug' => 'empresas',
            'level' => ModuleLevel::Basic,
            'status' => ModuleStatus::Active,
        ]);
    }
    enableModuleForCompany($session, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $session);

    setPermissionsTeamId($session->id);
    $names = ['empresas.access'];
    foreach ($abilities as $ability) {
        $names[] = "workplaces.{$ability}";
    }
    $user->givePermissionTo(collect($names)->map(fn (string $name) => Permission::findOrCreate($name))->all());
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

/** @return array{0: Company, 1: Company, 2: Company, 3: Workplace} matriz, D1, D2, sede de la matriz */
function matrixWithDelegations(): array
{
    $matrix = Company::factory()->create();
    $d1 = Company::factory()->create(['parent_id' => $matrix->id, 'name' => 'Delegacion A']);
    $d2 = Company::factory()->create(['parent_id' => $matrix->id, 'name' => 'Delegacion B']);
    $workplace = Workplace::factory()->create(['company_id' => $matrix->id]);

    return [$matrix, $d1, $d2, $workplace];
}

/** @return array<int, bool> company_id => is_preferred */
function workplaceLinks(Workplace $workplace): array
{
    return $workplace->delegations()
        ->get()
        ->mapWithKeys(fn (Company $c): array => [$c->id => (bool) $c->pivot->is_preferred])
        ->sortKeys()
        ->all();
}

it('vincula una sede de la matriz a varias delegaciones y sync reemplaza el set', function () {
    [$matrix, $d1, $d2, $workplace] = matrixWithDelegations();
    actingMapUser($matrix);

    $this->put(route('admin.workplaces.delegations.sync', $workplace), [
        'delegation_ids' => [$d1->id, $d2->id],
    ])->assertRedirect();

    expect(workplaceLinks($workplace))->toBe([$d1->id => false, $d2->id => false]);

    $this->put(route('admin.workplaces.delegations.sync', $workplace), [
        'delegation_ids' => [$d2->id],
    ])->assertRedirect();

    expect(workplaceLinks($workplace))->toBe([$d2->id => false]);

    $this->put(route('admin.workplaces.delegations.sync', $workplace), [
        'delegation_ids' => [],
    ])->assertRedirect();

    expect(workplaceLinks($workplace))->toBe([]);
});

it('permite el N:M inverso: una delegación con varias sedes de la matriz', function () {
    [$matrix, $d1, , $workplaceA] = matrixWithDelegations();
    $workplaceB = Workplace::factory()->create(['company_id' => $matrix->id]);
    actingMapUser($matrix);

    foreach ([$workplaceA, $workplaceB] as $workplace) {
        $this->put(route('admin.workplaces.delegations.sync', $workplace), [
            'delegation_ids' => [$d1->id],
        ])->assertRedirect();
    }

    expect($d1->workplacesViaMap()->pluck('workplaces.id')->sort()->values()->all())
        ->toBe(collect([$workplaceA->id, $workplaceB->id])->sort()->values()->all());
});

it('deja como máximo una preferida por workplace y cero es válido', function () {
    [$matrix, $d1, $d2, $workplace] = matrixWithDelegations();
    actingMapUser($matrix);
    $url = route('admin.workplaces.delegations.sync', $workplace);

    $this->put($url, ['delegation_ids' => [$d1->id, $d2->id], 'preferred_delegation_id' => $d1->id])
        ->assertRedirect();
    expect(workplaceLinks($workplace))->toBe([$d1->id => true, $d2->id => false]);

    $this->put($url, ['delegation_ids' => [$d1->id, $d2->id], 'preferred_delegation_id' => $d2->id])
        ->assertRedirect();
    expect(workplaceLinks($workplace))->toBe([$d1->id => false, $d2->id => true]);

    $this->put($url, ['delegation_ids' => [$d1->id, $d2->id], 'preferred_delegation_id' => null])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
    expect(workplaceLinks($workplace))->toBe([$d1->id => false, $d2->id => false]);
});

it('rechaza una preferida que no está entre las vinculadas', function () {
    [$matrix, $d1, $d2, $workplace] = matrixWithDelegations();
    actingMapUser($matrix);

    $this->from(route('admin.workplaces.edit', $workplace))
        ->put(route('admin.workplaces.delegations.sync', $workplace), [
            'delegation_ids' => [$d1->id],
            'preferred_delegation_id' => $d2->id,
        ])
        ->assertSessionHasErrors('preferred_delegation_id');

    expect(workplaceLinks($workplace))->toBe([]);
});

it('rechaza companies que no son hijas de la matriz de sesión (perímetro)', function () {
    [$matrix, $d1, , $workplace] = matrixWithDelegations();
    $otherMatrix = Company::factory()->create();
    $foreignChild = Company::factory()->create(['parent_id' => $otherMatrix->id]);
    $unrelated = Company::factory()->create();
    actingMapUser($matrix);
    $url = route('admin.workplaces.delegations.sync', $workplace);

    foreach ([$foreignChild->id, $unrelated->id, $otherMatrix->id, $matrix->id] as $badId) {
        $this->from(route('admin.workplaces.edit', $workplace))
            ->put($url, ['delegation_ids' => [$d1->id, $badId]])
            ->assertSessionHasErrors('delegation_ids.1');
    }

    expect(workplaceLinks($workplace))->toBe([]);
});

it('la Action rechaza ids fuera de la matriz aunque se salte el FormRequest', function () {
    [$matrix, $d1, , $workplace] = matrixWithDelegations();
    $foreignChild = Company::factory()->create(['parent_id' => Company::factory()->create()->id]);
    actingMapUser($matrix);

    expect(fn () => app(SyncWorkplaceDelegations::class)->handle($workplace, [$d1->id, $foreignChild->id]))
        ->toThrow(ValidationException::class);

    expect(workplaceLinks($workplace))->toBe([]);
});

it('403 si el workplace no es de la matriz de sesión y 404 por ruta si es de otra company (R-TEN-05)', function () {
    [$matrix, $d1, , $workplace] = matrixWithDelegations();
    $otherMatrix = Company::factory()->create();
    $otherChild = Company::factory()->create(['parent_id' => $otherMatrix->id]);
    $otherWorkplace = Workplace::factory()->create(['company_id' => $otherMatrix->id]);
    actingMapUser($matrix);

    $this->put(route('admin.workplaces.delegations.sync', $otherWorkplace), [
        'delegation_ids' => [$otherChild->id],
    ])->assertNotFound();

    expect($otherWorkplace->delegations()->count())->toBe(0);

    // Defensa en profundidad: la Action no confía en la ruta.
    expect(fn () => app(SyncWorkplaceDelegations::class)->handle($otherWorkplace, [$otherChild->id]))
        ->toThrow(AuthorizationException::class);

    expect($workplace->delegations()->count())->toBe(0)
        ->and($d1->workplacesViaMap()->count())->toBe(0);
});

it('la Action no mapea sedes de una Delegación (sesión no matriz)', function () {
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create(['parent_id' => $matrix->id]);
    $grandChild = Company::factory()->create(['parent_id' => $delegation->id]);
    $legalWorkplace = Workplace::factory()->create(['company_id' => $delegation->id]);
    actingMapUser($delegation);

    expect(fn () => app(SyncWorkplaceDelegations::class)->handle($legalWorkplace, [$grandChild->id]))
        ->toThrow(AuthorizationException::class);
});

it('sin workplaces.update responde 403', function () {
    [$matrix, $d1, , $workplace] = matrixWithDelegations();
    actingMapUser($matrix, ['view', 'viewAny']);

    $this->put(route('admin.workplaces.delegations.sync', $workplace), [
        'delegation_ids' => [$d1->id],
    ])->assertForbidden();

    expect($workplace->delegations()->count())->toBe(0);
});

it('Edit expone solo hijas de la matriz y refleja el mapa guardado (round-trip)', function () {
    [$matrix, $d1, $d2, $workplace] = matrixWithDelegations();
    Company::factory()->create(['parent_id' => Company::factory()->create()->id, 'name' => 'Hija ajena']);
    $trashed = Company::factory()->create(['parent_id' => $matrix->id, 'name' => 'Zeta borrada']);
    $trashed->delete();
    actingMapUser($matrix);

    $this->put(route('admin.workplaces.delegations.sync', $workplace), [
        'delegation_ids' => [$d1->id, $d2->id],
        'preferred_delegation_id' => $d2->id,
    ])->assertRedirect();

    $this->get(route('admin.workplaces.edit', $workplace))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Workplaces/Edit')
            ->where('delegation_map.options', [
                ['id' => $d1->id, 'name' => 'Delegacion A'],
                ['id' => $d2->id, 'name' => 'Delegacion B'],
            ])
            ->where('delegation_map.linked_ids', fn ($ids) => collect($ids)->sort()->values()->all() === collect([$d1->id, $d2->id])->sort()->values()->all())
            ->where('delegation_map.preferred_id', $d2->id));
});

it('Edit no muestra el mapa cuando la sesión no es matriz', function () {
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create(['parent_id' => $matrix->id]);
    $workplace = Workplace::factory()->create(['company_id' => $delegation->id]);
    actingMapUser($delegation);

    $this->get(route('admin.workplaces.edit', $workplace))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('delegation_map', null));
});

it('UI Edit Workplace incluye el panel de delegaciones con i18n', function () {
    $edit = file_get_contents(resource_path('js/Pages/Admin/Workplaces/Edit.tsx'));
    $panel = file_get_contents(resource_path('js/Pages/Admin/Workplaces/components/WorkplaceDelegationsPanel.tsx'));

    expect($edit)->toContain('WorkplaceDelegationsPanel')
        ->and($panel)->toContain("t('delegaciones_sede')")
        ->and($panel)->toContain("t('delegacion_preferida')")
        ->and($panel)->toContain('/delegations');
});
