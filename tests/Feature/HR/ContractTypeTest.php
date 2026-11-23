<?php

use App\Domains\Admin\Models\Company;
use App\Domains\HR\Enums\ContractTypeStatus;
use App\Domains\HR\Models\ContractType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/HrTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('CRUD de tipos de contrato de la company en sesión con slug y auditoría', function () {
    $company = Company::factory()->create();
    $user = actingHrUser($company);

    $this->post(route('hr.contract-types.store'), [
        'name' => 'Indefinido',
        'duration' => null,
        'with_holidays' => true,
        'status' => ContractTypeStatus::Active->value,
    ])->assertRedirect();

    $type = ContractType::query()->where('name', 'Indefinido')->firstOrFail();

    expect($type->company_id)->toBe($company->id)
        ->and($type->slug)->toBe('indefinido')
        ->and($type->duration)->toBeNull()
        ->and($type->with_holidays)->toBeTrue()
        ->and($type->status)->toBe(ContractTypeStatus::Active)
        ->and($type->created_by)->toBe($user->id)
        ->and($type->updated_by)->toBe($user->id);

    $this->put(route('hr.contract-types.update', $type), [
        'name' => 'Indefinido jornada completa',
        'duration' => 365,
        'with_holidays' => false,
        'status' => ContractTypeStatus::Inactive->value,
    ])->assertRedirect();

    $type->refresh();

    expect($type->name)->toBe('Indefinido jornada completa')
        ->and($type->slug)->toBe('indefinido') // slug inmutable
        ->and($type->duration)->toBe(365)
        ->and($type->with_holidays)->toBeFalse()
        ->and($type->status)->toBe(ContractTypeStatus::Inactive)
        ->and($type->updated_by)->toBe($user->id);

    $this->get(route('hr.contract-types.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.slug', 'indefinido')
        ->assertJsonPath('data.0.duration', 365);

    $this->delete(route('hr.contract-types.destroy', $type))
        ->assertRedirect(route('hr.contract-types.index'));

    expect(ContractType::query()->whereKey($type->id)->exists())->toBeFalse()
        ->and(ContractType::withTrashed()->whereKey($type->id)->exists())->toBeTrue();
});

it('Index y Create renderizan las páginas Inertia de HR', function () {
    actingHrUser(Company::factory()->create());

    $this->get(route('hr.contract-types.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('HR/ContractTypes/Index'));

    $this->get(route('hr.contract-types.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('HR/ContractTypes/Create'));
});

it('genera slugs únicos por company y repetibles entre companies', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    actingHrUser($companyA);
    $payload = ['name' => 'Temporal', 'with_holidays' => false, 'status' => 'active'];
    $this->post(route('hr.contract-types.store'), $payload)->assertRedirect();
    $this->post(route('hr.contract-types.store'), $payload)->assertRedirect();

    $slugsA = ContractType::query()->pluck('slug')->all();
    expect($slugsA)->toHaveCount(2)
        ->and($slugsA[0])->toBe('temporal')
        ->and($slugsA[1])->not->toBe('temporal');

    actingHrUser($companyB);
    $this->post(route('hr.contract-types.store'), $payload)->assertRedirect();

    expect(ContractType::query()->pluck('slug')->all())->toBe(['temporal']);
});

it('valida name, duration y status', function () {
    actingHrUser(Company::factory()->create());

    $this->from(route('hr.contract-types.create'))
        ->post(route('hr.contract-types.store'), [
            'name' => '',
            'indefinite' => false,
            'duration' => 0,
            'status' => 'draft',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['name', 'duration', 'status']);
});

it('persiste indefinido con duration null y rechaza temporal sin días', function () {
    actingHrUser(Company::factory()->create());

    $this->post(route('hr.contract-types.store'), [
        'name' => 'Fijo indefinido',
        'indefinite' => true,
        'duration' => 365,
        'with_holidays' => true,
        'status' => ContractTypeStatus::Active->value,
    ])->assertRedirect();

    expect(ContractType::query()->where('name', 'Fijo indefinido')->value('duration'))->toBeNull();

    $this->from(route('hr.contract-types.create'))
        ->post(route('hr.contract-types.store'), [
            'name' => 'Temporal sin días',
            'indefinite' => false,
            'duration' => null,
            'status' => ContractTypeStatus::Active->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('duration');
});

it('un tipo soft-deleted no se ofrece en altas nuevas pero conserva el histórico', function () {
    $company = Company::factory()->create();
    actingHrUser($company);

    $kept = ContractType::factory()->create(['company_id' => $company->id, 'name' => 'Vigente']);
    $deleted = ContractType::factory()->create(['company_id' => $company->id, 'name' => 'Borrado']);
    $inactive = ContractType::factory()->inactive()->create(['company_id' => $company->id, 'name' => 'Inactivo']);
    $deleted->delete();

    $this->get(route('hr.employees.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('HR/Employees/Create')
            ->has('options.contract_types', 1)
            ->where('options.contract_types.0.id', $kept->id));

    expect(ContractType::query()->pluck('id')->all())
        ->toContain($kept->id, $inactive->id)
        ->not->toContain($deleted->id)
        ->and(ContractType::withTrashed()->whereKey($deleted->id)->exists())->toBeTrue();
});

it('aísla tipos de contrato entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $typeA = ContractType::factory()->create(['company_id' => $companyA->id, 'name' => 'Tipo A']);
    $typeB = ContractType::factory()->create(['company_id' => $companyB->id, 'name' => 'Tipo B']);

    actingHrUser($companyA);

    expect(ContractType::query()->pluck('id')->all())
        ->toContain($typeA->id)
        ->not->toContain($typeB->id);

    $this->get(route('hr.contract-types.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Tipo A');

    $this->get(route('hr.contract-types.edit', $typeB))->assertNotFound();
    $this->put(route('hr.contract-types.update', $typeB), [
        'name' => 'Hacked',
        'status' => 'active',
    ])->assertNotFound();
    $this->delete(route('hr.contract-types.destroy', $typeB))->assertNotFound();

    expect($typeB->fresh()->name)->toBe('Tipo B');
});

it('responde 403 sin permiso de contract-types', function () {
    $company = Company::factory()->create();
    $type = ContractType::factory()->create(['company_id' => $company->id]);

    actingHrUser($company, ['rrhh.access']);

    $this->get(route('hr.contract-types.index'))->assertForbidden();
    $this->get(route('hr.contract-types.table'))->assertForbidden();
    $this->get(route('hr.contract-types.create'))->assertForbidden();
    $this->post(route('hr.contract-types.store'), ['name' => 'X', 'status' => 'active'])->assertForbidden();
    $this->get(route('hr.contract-types.edit', $type))->assertForbidden();
    $this->put(route('hr.contract-types.update', $type), ['name' => 'X', 'status' => 'active'])->assertForbidden();
    $this->delete(route('hr.contract-types.destroy', $type))->assertForbidden();
});
