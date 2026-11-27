<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Calendar\Models\DayType;
use App\Domains\Calendar\Models\WorkCalendar;
use App\Domains\Calendar\Models\WorkCalendarDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/HrTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('find-or-create mantiene un WorkCalendar 1:1 por workplace', function () {
    $company = Company::factory()->create();
    $workplace = Workplace::factory()->create(['company_id' => $company->id]);
    actingHrUser($company);

    $this->get(route('rrhh.calendars.index', [
        'workplace_id' => $workplace->id,
        'year' => 2026,
    ]))->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('HR/Calendars/Index')
            ->where('calendar.workplace_id', $workplace->id)
            ->where('year', 2026));

    expect(WorkCalendar::query()->where('workplace_id', $workplace->id)->count())->toBe(1);

    $this->get(route('rrhh.calendars.index', [
        'workplace_id' => $workplace->id,
        'year' => 2027,
    ]))->assertOk();

    expect(WorkCalendar::query()->where('workplace_id', $workplace->id)->count())->toBe(1);
});

it('upsert y delete de día con tipo de la misma company', function () {
    $company = Company::factory()->create();
    $workplace = Workplace::factory()->create(['company_id' => $company->id]);
    $dayType = DayType::factory()->create(['company_id' => $company->id, 'name' => 'Festivo']);
    $user = actingHrUser($company);

    $this->get(route('rrhh.calendars.index', ['workplace_id' => $workplace->id, 'year' => 2026]));
    $calendar = WorkCalendar::query()->where('workplace_id', $workplace->id)->firstOrFail();

    $this->post(route('rrhh.calendars.days.upsert', $calendar), [
        'date' => '2026-01-06',
        'name' => 'Reyes',
        'day_type_id' => $dayType->id,
    ])->assertRedirect();

    $day = WorkCalendarDay::query()->where('work_calendar_id', $calendar->id)->firstOrFail();

    expect($day->name)->toBe('Reyes')
        ->and($day->day_type_id)->toBe($dayType->id)
        ->and($day->created_by)->toBe($user->id);

    $this->post(route('rrhh.calendars.days.upsert', $calendar), [
        'date' => '2026-01-06',
        'name' => 'Reyes Magos',
        'day_type_id' => $dayType->id,
    ])->assertRedirect();

    expect(WorkCalendarDay::query()->where('work_calendar_id', $calendar->id)->count())->toBe(1)
        ->and($day->fresh()->name)->toBe('Reyes Magos')
        ->and($day->fresh()->updated_by)->toBe($user->id);

    $this->delete(route('rrhh.calendars.days.destroy', [$calendar, $day]))
        ->assertRedirect();

    expect(WorkCalendarDay::query()->whereKey($day->id)->exists())->toBeFalse();
});

it('rechaza day_type de otra company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $workplace = Workplace::factory()->create(['company_id' => $companyA->id]);
    $foreignType = DayType::factory()->create(['company_id' => $companyB->id]);

    actingHrUser($companyA);
    $this->get(route('rrhh.calendars.index', ['workplace_id' => $workplace->id, 'year' => 2026]));
    $calendar = WorkCalendar::query()->where('workplace_id', $workplace->id)->firstOrFail();

    $this->from(route('rrhh.calendars.index', ['workplace_id' => $workplace->id]))
        ->post(route('rrhh.calendars.days.upsert', $calendar), [
            'date' => '2026-05-01',
            'name' => 'Hack',
            'day_type_id' => $foreignType->id,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['day_type_id']);
});

it('hereda días de un año a otro omitiendo 29-feb si destino no bisiesto', function () {
    $company = Company::factory()->create();
    $workplace = Workplace::factory()->create(['company_id' => $company->id]);
    $dayType = DayType::factory()->create(['company_id' => $company->id]);
    actingHrUser($company);

    $this->get(route('rrhh.calendars.index', ['workplace_id' => $workplace->id, 'year' => 2024]));
    $calendar = WorkCalendar::query()->where('workplace_id', $workplace->id)->firstOrFail();

    WorkCalendarDay::factory()->create([
        'work_calendar_id' => $calendar->id,
        'date' => '2024-01-01',
        'name' => 'Año Nuevo',
        'day_type_id' => $dayType->id,
    ]);
    WorkCalendarDay::factory()->create([
        'work_calendar_id' => $calendar->id,
        'date' => '2024-02-29',
        'name' => 'Bisiesto',
        'day_type_id' => $dayType->id,
    ]);

    $this->post(route('rrhh.calendars.inherit', $calendar), [
        'from_year' => 2024,
        'to_year' => 2025,
    ])->assertRedirect(route('rrhh.calendars.index', [
        'workplace_id' => $workplace->id,
        'year' => 2025,
    ]));

    $dates = WorkCalendarDay::query()
        ->where('work_calendar_id', $calendar->id)
        ->whereYear('date', 2025)
        ->pluck('date')
        ->map(fn ($d) => $d->toDateString())
        ->all();

    expect($dates)->toContain('2025-01-01')
        ->and($dates)->not->toContain('2025-02-29');
});

it('copia días de un año entre sedes de la misma company', function () {
    $company = Company::factory()->create();
    $w1 = Workplace::factory()->create(['company_id' => $company->id, 'name' => 'Sede 1']);
    $w2 = Workplace::factory()->create(['company_id' => $company->id, 'name' => 'Sede 2']);
    $dayType = DayType::factory()->create(['company_id' => $company->id]);
    actingHrUser($company);

    $this->get(route('rrhh.calendars.index', ['workplace_id' => $w1->id, 'year' => 2026]));
    $source = WorkCalendar::query()->where('workplace_id', $w1->id)->firstOrFail();

    WorkCalendarDay::factory()->create([
        'work_calendar_id' => $source->id,
        'date' => '2026-12-25',
        'name' => 'Navidad',
        'day_type_id' => $dayType->id,
    ]);

    $this->post(route('rrhh.calendars.copy', $source), [
        'year' => 2026,
        'target_workplace_id' => $w2->id,
    ])->assertRedirect(route('rrhh.calendars.index', [
        'workplace_id' => $w2->id,
        'year' => 2026,
    ]));

    $target = WorkCalendar::query()->where('workplace_id', $w2->id)->firstOrFail();
    $copied = WorkCalendarDay::query()
        ->where('work_calendar_id', $target->id)
        ->whereDate('date', '2026-12-25')
        ->first();

    expect($copied)->not->toBeNull()
        ->and($copied->name)->toBe('Navidad')
        ->and($copied->day_type_id)->toBe($dayType->id);
});

it('aísla calendarios entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $workplaceB = Workplace::factory()->create(['company_id' => $companyB->id]);
    $calendarB = WorkCalendar::factory()->forWorkplace($workplaceB)->create();

    actingHrUser($companyA);

    $this->get(route('rrhh.calendars.index', [
        'workplace_id' => $workplaceB->id,
        'year' => 2026,
    ]))->assertNotFound();

    $this->post(route('rrhh.calendars.days.upsert', $calendarB), [
        'date' => '2026-01-01',
        'name' => 'Hack',
        'day_type_id' => 1,
    ])->assertNotFound();
});

it('incluye Calendarios en el menú RRHH (V-08)', function () {
    $user = actingHrUser(Company::factory()->create());

    $rrhh = collect(app(MenuBuilder::class)->forUser($user))
        ->firstWhere('slug', 'rrhh');
    $items = collect($rrhh['functionalities'] ?? [])->keyBy('slug');

    expect($items->has('calendars'))->toBeTrue()
        ->and($items['calendars']['href'])->toBe(route('rrhh.calendars.index', absolute: false));
});
