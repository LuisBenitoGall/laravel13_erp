<?php

use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\ContractType;
use App\Domains\HR\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/HrTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function employeePayload(Company $company, User $member, array $overrides = []): array
{
    $type = ContractType::query()->where('company_id', $company->id)->first()
        ?? ContractType::factory()->indefinite()->create(['company_id' => $company->id]);

    return array_merge([
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'workplace_id' => null,
        'job_title' => 'Operario',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => null,
        'weekly_hours' => '37.50',
        'time_control' => true,
        'year_salary' => '18000.50',
        'observations' => 'Notas',
    ], $overrides);
}

it('da de alta un periodo con company_id de sesión, casts decimales y auditoría', function () {
    $company = Company::factory()->create();
    $actor = actingHrUser($company);
    $member = companyMember($company);

    $this->post(route('hr.employees.store'), employeePayload($company, $member))
        ->assertRedirect();

    $employee = Employee::query()->firstOrFail();

    expect($employee->company_id)->toBe($company->id)
        ->and($employee->user_id)->toBe($member->id)
        ->and($employee->status)->toBe(EmployeeStatus::Active)
        ->and($employee->weekly_hours)->toBe('37.50')
        ->and($employee->year_salary)->toBe('18000.50')
        ->and($employee->time_control)->toBeTrue()
        ->and($employee->created_by)->toBe($actor->id)
        ->and($employee->updated_by)->toBe($actor->id);
});

it('un periodo scheduled que solapa un active indefinido se rechaza (opción B)', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    $active = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'end_date' => null,
        'status' => EmployeeStatus::Active,
    ]);

    // Sin cerrar el active (Renew / Create→active), no se puede programar solapado.
    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'start_date' => now()->addMonth()->toDateString(),
    ]))->assertSessionHasErrors('start_date');

    expect($active->fresh()->status)->toBe(EmployeeStatus::Active)
        ->and(Employee::query()->where('status', EmployeeStatus::Scheduled)->count())->toBe(0);
});

it('un periodo con inicio futuro nace scheduled si no hay solape con no-ended', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    Employee::factory()->ended()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'start_date' => now()->subYear()->toDateString(),
        'end_date' => now()->subMonths(6)->toDateString(),
    ]);

    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'start_date' => now()->addMonth()->toDateString(),
    ]))->assertRedirect();

    $scheduled = Employee::query()->where('status', EmployeeStatus::Scheduled)->firstOrFail();

    expect($scheduled->user_id)->toBe($member->id);
});

it('rechaza el alta de un User sin membresía en la company', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $outsider = User::factory()->create(['status' => true]); // sin user_companies

    $this->from(route('hr.employees.create'))
        ->post(route('hr.employees.store'), employeePayload($company, $outsider))
        ->assertRedirect()
        ->assertSessionHasErrors('user_id');

    // Miembro de OTRA company: tampoco vale.
    $otherCompany = Company::factory()->create();
    $otherMember = companyMember($otherCompany);

    $this->post(route('hr.employees.store'), employeePayload($company, $otherMember))
        ->assertSessionHasErrors('user_id');

    // User inexistente: el flujo no crea Users.
    $this->post(route('hr.employees.store'), employeePayload($company, $outsider, ['user_id' => 999999]))
        ->assertSessionHasErrors('user_id');

    expect(Employee::query()->count())->toBe(0)
        ->and(User::query()->whereKey(999999)->exists())->toBeFalse();
});

it('rechaza ContractType y Workplace de otra company o no ofertables', function () {
    $company = Company::factory()->create();
    $other = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    $foreignType = ContractType::factory()->create(['company_id' => $other->id]);
    $foreignWorkplace = Workplace::factory()->create(['company_id' => $other->id]);
    $inactiveType = ContractType::factory()->inactive()->create(['company_id' => $company->id]);
    $inactiveWorkplace = Workplace::factory()->inactive()->create(['company_id' => $company->id]);

    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'contract_type_id' => $foreignType->id,
        'workplace_id' => $foreignWorkplace->id,
    ]))->assertSessionHasErrors(['contract_type_id', 'workplace_id']);

    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'contract_type_id' => $inactiveType->id,
        'workplace_id' => $inactiveWorkplace->id,
    ]))->assertSessionHasErrors(['contract_type_id', 'workplace_id']);

    $okWorkplace = Workplace::factory()->create([
        'company_id' => $company->id,
        'status' => WorkplaceStatus::Active,
    ]);

    $okType = ContractType::factory()->create(['company_id' => $company->id]);

    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'contract_type_id' => $okType->id,
        'workplace_id' => $okWorkplace->id,
    ]))->assertSessionHasNoErrors()->assertRedirect();

    expect(Employee::query()->count())->toBe(1);
});

it('valida fechas, horas y salario', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-01',
        'weekly_hours' => '200',
        'year_salary' => '-1',
    ]))->assertSessionHasErrors(['end_date', 'weekly_hours', 'year_salary']);
});

it('mantiene como máximo un periodo active por user y company', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    $first = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'start_date' => now()->subYear()->toDateString(),
    ]);

    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'start_date' => now()->subDay()->toDateString(),
    ]))->assertRedirect();

    $first->refresh();
    $active = Employee::query()->where('user_id', $member->id)->where('status', EmployeeStatus::Active)->get();

    expect($active)->toHaveCount(1)
        ->and($active->first()->id)->not->toBe($first->id)
        ->and($first->status)->toBe(EmployeeStatus::Ended)
        ->and($first->end_date->toDateString())->toBe(now()->subDays(2)->toDateString());

    // Otro User de la misma company no se ve afectado.
    $other = companyMember($company);
    Employee::factory()->create(['company_id' => $company->id, 'user_id' => $other->id]);

    expect(Employee::query()->where('status', EmployeeStatus::Active)->count())->toBe(2);
});

it('renovar cierra el periodo activo y crea uno nuevo en la misma transacción', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    $current = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'start_date' => now()->subYear()->toDateString(),
    ]);
    $newType = ContractType::factory()->create(['company_id' => $company->id, 'name' => 'Indefinido']);

    $this->get(route('hr.employees.renew.form', $current))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('HR/Employees/Renew'));

    $this->post(route('hr.employees.renew', $current), employeePayload($company, $member, [
        'contract_type_id' => $newType->id,
        'start_date' => now()->toDateString(),
    ]))->assertRedirect();

    $current->refresh();
    $renewed = Employee::query()->where('status', EmployeeStatus::Active)->firstOrFail();

    expect($renewed->id)->not->toBe($current->id)
        ->and($renewed->user_id)->toBe($member->id)
        ->and($renewed->contract_type_id)->toBe($newType->id)
        ->and($current->status)->toBe(EmployeeStatus::Ended)
        ->and($current->end_date->toDateString())->toBe(now()->subDay()->toDateString())
        ->and(Employee::query()->where('user_id', $member->id)->count())->toBe(2)
        ->and(Employee::query()->where('status', EmployeeStatus::Active)->count())->toBe(1);

    // El histórico del User aparece en Edit.
    $this->get(route('hr.employees.edit', $renewed))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('HR/Employees/Edit')
            ->has('history', 2)
            ->where('history.0.id', $renewed->id));
});

it('no permite renovar ni finalizar un periodo que no está active', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    $ended = Employee::factory()->ended()->create(['company_id' => $company->id, 'user_id' => $member->id]);

    $this->post(route('hr.employees.renew', $ended), employeePayload($company, $member))
        ->assertSessionHasErrors('employee');

    $this->post(route('hr.employees.end', $ended))->assertSessionHasErrors('employee');

    expect(Employee::query()->count())->toBe(1);
});

it('finalizar cierra el periodo activo con la fecha indicada o hoy', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    $employee = Employee::factory()->create(['company_id' => $company->id, 'user_id' => $member->id]);

    $this->post(route('hr.employees.end', $employee))->assertRedirect();

    $employee->refresh();

    expect($employee->status)->toBe(EmployeeStatus::Ended)
        ->and($employee->end_date->toDateString())->toBe(now()->toDateString());

    $second = Employee::factory()->create(['company_id' => $company->id, 'user_id' => $member->id]);

    $this->post(route('hr.employees.end', $second), ['end_date' => now()->addWeek()->toDateString()])
        ->assertRedirect();

    expect($second->fresh()->end_date->toDateString())->toBe(now()->addWeek()->toDateString());
});

it('update edita el periodo sin cambiar user ni status', function () {
    $company = Company::factory()->create();
    $actor = actingHrUser($company);
    $member = companyMember($company);
    $employee = Employee::factory()->create(['company_id' => $company->id, 'user_id' => $member->id]);
    $intruder = companyMember($company);

    $this->put(route('hr.employees.update', $employee), employeePayload($company, $intruder, [
        'job_title' => 'Encargado',
        'weekly_hours' => '20',
        'status' => 'ended',
    ]))->assertRedirect();

    $employee->refresh();

    expect($employee->job_title)->toBe('Encargado')
        ->and($employee->weekly_hours)->toBe('20.00')
        ->and($employee->user_id)->toBe($member->id)
        ->and($employee->status)->toBe(EmployeeStatus::Active)
        ->and($employee->updated_by)->toBe($actor->id);
});

it('Create ofrece solo miembros de la company y Edit conserva tipo borrado del histórico', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);
    $outsider = User::factory()->create(['status' => true]);

    $this->get(route('hr.employees.create'))
        ->assertOk()
        ->assertInertia(function ($page) use ($member, $outsider) {
            $ids = collect($page->toArray()['props']['options']['users'])->pluck('id');

            expect($ids)->toContain($member->id)->not->toContain($outsider->id);
        });

    $type = ContractType::factory()->create(['company_id' => $company->id]);
    $employee = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
    ]);
    $type->delete();

    expect($employee->fresh()->contractType->id)->toBe($type->id);

    $this->get(route('hr.employees.edit', $employee))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('options.contract_types', 1)
            ->where('options.contract_types.0.id', $type->id)
            ->where('employee.contract_type_name', $type->name));
});

it('lista los periodos de la company con filtro por estado', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);
    $type = ContractType::factory()->create(['company_id' => $company->id]);

    Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
    ]);
    Employee::factory()->ended()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
    ]);

    $this->get(route('hr.employees.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('HR/Employees/Index')
            ->has('contract_type_filter')
            ->where('contract_type_filter.'.(string) $type->id, $type->name));

    $this->get(route('hr.employees.table'))
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonPath('data.0.status', 'active')
        ->assertJsonPath('data.0.user_name', $member->name);

    $this->getJson(route('hr.employees.table', [
        'filter' => [['field' => 'status', 'type' => '=', 'value' => 'ended']],
    ]))->assertJsonPath('total', 1)->assertJsonPath('data.0.status', 'ended');

    $this->getJson(route('hr.employees.table', [
        'filter' => [['field' => 'user_name', 'type' => 'like', 'value' => $member->name]],
    ]))->assertJsonPath('total', 2);

    $this->getJson(route('hr.employees.table', [
        'filter' => [['field' => 'user_name', 'type' => 'like', 'value' => 'zzz-no-match']],
    ]))->assertJsonPath('total', 0);

    $this->getJson(route('hr.employees.table', [
        'filter' => [['field' => 'contract_type_id', 'type' => '=', 'value' => (string) $type->id]],
    ]))->assertJsonPath('total', 2);
});

it('aísla empleados entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $memberA = companyMember($companyA);
    $memberB = companyMember($companyB);
    $employeeA = Employee::factory()->create(['company_id' => $companyA->id, 'user_id' => $memberA->id]);
    $employeeB = Employee::factory()->create(['company_id' => $companyB->id, 'user_id' => $memberB->id]);

    actingHrUser($companyA);

    expect(Employee::query()->pluck('id')->all())
        ->toContain($employeeA->id)
        ->not->toContain($employeeB->id);

    $this->get(route('hr.employees.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $employeeA->id);

    $this->get(route('hr.employees.edit', $employeeB))->assertNotFound();
    $this->put(route('hr.employees.update', $employeeB), employeePayload($companyA, $memberA))->assertNotFound();
    $this->get(route('hr.employees.renew.form', $employeeB))->assertNotFound();
    $this->post(route('hr.employees.renew', $employeeB), employeePayload($companyA, $memberA))->assertNotFound();
    $this->post(route('hr.employees.end', $employeeB))->assertNotFound();
    $this->delete(route('hr.employees.destroy', $employeeB))->assertNotFound();

    expect($employeeB->fresh()->status)->toBe(EmployeeStatus::Active);
});

it('soft-delete de un periodo lo oculta del listado', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);
    $employee = Employee::factory()->create(['company_id' => $company->id, 'user_id' => $member->id]);

    $this->delete(route('hr.employees.destroy', $employee))
        ->assertRedirect(route('hr.employees.index'));

    expect(Employee::query()->count())->toBe(0)
        ->and(Employee::withTrashed()->count())->toBe(1);
});

it('responde 403 sin permisos de employees', function () {
    $company = Company::factory()->create();
    $member = companyMember($company);
    $employee = Employee::factory()->create(['company_id' => $company->id, 'user_id' => $member->id]);

    actingHrUser($company, ['rrhh.access']);

    $this->get(route('hr.employees.index'))->assertForbidden();
    $this->get(route('hr.employees.table'))->assertForbidden();
    $this->get(route('hr.employees.create'))->assertForbidden();
    $this->post(route('hr.employees.store'), employeePayload($company, $member))->assertForbidden();
    $this->get(route('hr.employees.edit', $employee))->assertForbidden();
    $this->put(route('hr.employees.update', $employee), employeePayload($company, $member))->assertForbidden();
    $this->get(route('hr.employees.renew.form', $employee))->assertForbidden();
    $this->post(route('hr.employees.renew', $employee), employeePayload($company, $member))->assertForbidden();
    $this->post(route('hr.employees.end', $employee))->assertForbidden();
    $this->delete(route('hr.employees.destroy', $employee))->assertForbidden();
});

it('renovar exige employees.create además de employees.update', function () {
    $company = Company::factory()->create();
    $member = companyMember($company);
    $employee = Employee::factory()->create(['company_id' => $company->id, 'user_id' => $member->id]);

    actingHrUser($company, ['rrhh.access', 'employees.view', 'employees.update']);

    $this->get(route('hr.employees.renew.form', $employee))->assertForbidden();
    $this->post(route('hr.employees.end', $employee))->assertRedirect();
});

it('exige end_date cuando el tipo tiene duration y permite null si es indefinido', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);

    $fixed = ContractType::factory()->fixed(90)->create(['company_id' => $company->id]);
    $indefinite = ContractType::factory()->indefinite()->create(['company_id' => $company->id]);

    $this->from(route('hr.employees.create'))
        ->post(route('hr.employees.store'), employeePayload($company, $member, [
            'contract_type_id' => $fixed->id,
            'end_date' => null,
        ]))
        ->assertRedirect()
        ->assertSessionHasErrors('end_date');

    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'contract_type_id' => $fixed->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-03-31',
    ]))->assertRedirect()->assertSessionHasNoErrors();

    $this->post(route('hr.employees.store'), employeePayload($company, $member, [
        'user_id' => companyMember($company)->id,
        'contract_type_id' => $indefinite->id,
        'end_date' => null,
    ]))->assertRedirect()->assertSessionHasNoErrors();

    expect(Employee::query()->whereNull('end_date')->count())->toBe(1)
        ->and(Employee::query()->whereNotNull('end_date')->count())->toBe(1);
});
