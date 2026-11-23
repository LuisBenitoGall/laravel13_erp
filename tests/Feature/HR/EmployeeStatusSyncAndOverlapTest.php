<?php

use App\Domains\Admin\Models\Company;
use App\Domains\HR\Actions\SyncEmployeeStatuses;
use App\Domains\HR\Enums\EmployeeStatus;
use App\Domains\HR\Models\ContractType;
use App\Domains\HR\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/HrTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Carbon::setTestNow(Carbon::parse('2026-11-25 12:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('sync: scheduled→active; active→ended; indefinido intacto; scheduled bloqueado si sigue active', function () {
    $company = Company::factory()->create();
    $member = User::factory()->create(['status' => true]);
    $company->users()->attach($member->id);
    $type = ContractType::factory()->indefinite()->create(['company_id' => $company->id]);

    $toActivate = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-11-20',
        'end_date' => null,
        'status' => EmployeeStatus::Scheduled,
    ]);

    $otherUser = User::factory()->create(['status' => true]);
    $company->users()->attach($otherUser->id);

    $toExpire = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $otherUser->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-11-20',
        'status' => EmployeeStatus::Active,
    ]);

    $indefiniteUser = User::factory()->create(['status' => true]);
    $company->users()->attach($indefiniteUser->id);

    $indefinite = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $indefiniteUser->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-01-01',
        'end_date' => null,
        'status' => EmployeeStatus::Active,
    ]);

    $blockedUser = User::factory()->create(['status' => true]);
    $company->users()->attach($blockedUser->id);

    $blockingActive = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $blockedUser->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-01-01',
        'end_date' => null,
        'status' => EmployeeStatus::Active,
    ]);

    $blockedScheduled = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $blockedUser->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-11-01',
        'end_date' => null,
        'status' => EmployeeStatus::Scheduled,
    ]);

    $result = app(SyncEmployeeStatuses::class)->handle();

    expect($result['expired'])->toBe(1)
        ->and($result['activated'])->toBe(1)
        ->and($toActivate->fresh()->status)->toBe(EmployeeStatus::Active)
        ->and($toExpire->fresh()->status)->toBe(EmployeeStatus::Ended)
        ->and($indefinite->fresh()->status)->toBe(EmployeeStatus::Active)
        ->and($indefinite->fresh()->end_date)->toBeNull()
        ->and($blockingActive->fresh()->status)->toBe(EmployeeStatus::Active)
        ->and($blockedScheduled->fresh()->status)->toBe(EmployeeStatus::Scheduled);

    Artisan::call('hr:sync-employee-statuses');
    expect(Artisan::output())->toContain('expired:');

    $events = collect(Schedule::events())
        ->map(fn ($event) => $event->command ?? $event->description ?? '')
        ->implode(' ');
    expect($events)->toContain('hr:sync-employee-statuses');
});

it('opción B: rechaza dos scheduled solapados (caso ACME); día siguiente OK', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);
    $type = ContractType::factory()->indefinite()->create(['company_id' => $company->id]);
    $temporal = ContractType::factory()->create(['company_id' => $company->id]);

    // Indefinido scheduled 25/11→∞
    Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-11-25',
        'end_date' => null,
        'status' => EmployeeStatus::Scheduled,
    ]);

    // Temporal scheduled 24/11–10/01 → solapa → rechazo
    $this->post(route('hr.employees.store'), [
        'user_id' => $member->id,
        'contract_type_id' => $temporal->id,
        'start_date' => '2026-11-24',
        'end_date' => '2027-01-10',
        'weekly_hours' => 40,
        'time_control' => true,
    ])->assertSessionHasErrors('start_date');

    expect(Employee::query()->where('user_id', $member->id)->count())->toBe(1);

    // Fin 24/11 + nuevo start 25/11 sin solape con otros → OK
    Employee::query()->where('user_id', $member->id)->delete();
    Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $temporal->id,
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-24',
        'status' => EmployeeStatus::Scheduled,
    ]);

    $this->post(route('hr.employees.store'), [
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-11-25',
        'end_date' => null,
        'weekly_hours' => 40,
        'time_control' => true,
    ])->assertRedirect();

    expect(Employee::query()->where('user_id', $member->id)->count())->toBe(2);
});

it('Create que nace active cierra el active previo aunque solape en calendario', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);
    $type = ContractType::factory()->indefinite()->create(['company_id' => $company->id]);

    Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-11-24',
        'status' => EmployeeStatus::Active,
    ]);

    // Hoy = 25/11 → start 20/11 nace active; exceptIds evita autobloqueo.
    $this->post(route('hr.employees.store'), [
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-11-20',
        'end_date' => null,
        'weekly_hours' => 40,
        'time_control' => true,
    ])->assertRedirect();

    expect(Employee::query()->where('user_id', $member->id)->where('status', EmployeeStatus::Active)->count())
        ->toBe(1)
        ->and(Employee::query()->where('user_id', $member->id)->where('status', EmployeeStatus::Ended)->count())
        ->toBe(1)
        ->and(Employee::query()->where('status', EmployeeStatus::Ended)->value('end_date')?->toDateString())
        ->toBe('2026-11-24');
});

it('Create scheduled vs active que solapa se rechaza (active no se cierra)', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);
    $type = ContractType::factory()->indefinite()->create(['company_id' => $company->id]);

    Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-01-01',
        'end_date' => null,
        'status' => EmployeeStatus::Active,
    ]);

    $this->post(route('hr.employees.store'), [
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-12-01',
        'end_date' => '2026-12-31',
        'weekly_hours' => 40,
        'time_control' => true,
    ])->assertSessionHasErrors('start_date');
});

it('renovar indefinido cierra con fin D-1', function () {
    $company = Company::factory()->create();
    actingHrUser($company);
    $member = companyMember($company);
    $type = ContractType::factory()->indefinite()->create(['company_id' => $company->id]);

    $active = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'contract_type_id' => $type->id,
        'start_date' => '2026-01-01',
        'end_date' => null,
        'status' => EmployeeStatus::Active,
    ]);

    $this->post(route('hr.employees.renew', $active), [
        'contract_type_id' => $type->id,
        'start_date' => '2026-11-25',
        'end_date' => null,
        'weekly_hours' => 40,
        'time_control' => true,
    ])->assertRedirect();

    expect($active->fresh()->status)->toBe(EmployeeStatus::Ended)
        ->and($active->fresh()->end_date?->toDateString())->toBe('2026-11-24')
        ->and(Employee::query()->where('user_id', $member->id)->where('status', EmployeeStatus::Active)->count())
        ->toBe(1);
});

it('permite solape de fechas entre companies distintas', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $member = User::factory()->create(['status' => true]);
    $companyA->users()->attach($member->id);
    $companyB->users()->attach($member->id);

    $typeA = ContractType::factory()->indefinite()->create(['company_id' => $companyA->id]);
    $typeB = ContractType::factory()->indefinite()->create(['company_id' => $companyB->id]);

    Employee::factory()->create([
        'company_id' => $companyA->id,
        'user_id' => $member->id,
        'contract_type_id' => $typeA->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => EmployeeStatus::Active,
    ]);

    actingHrUser($companyB);

    $this->post(route('hr.employees.store'), [
        'user_id' => $member->id,
        'contract_type_id' => $typeB->id,
        'start_date' => '2026-06-01',
        'end_date' => null,
        'weekly_hours' => 40,
        'time_control' => true,
    ])->assertRedirect();

    expect(
        Employee::query()->withoutGlobalScope('company')
            ->where('user_id', $member->id)
            ->where('status', EmployeeStatus::Active)
            ->count(),
    )->toBe(2);
});
