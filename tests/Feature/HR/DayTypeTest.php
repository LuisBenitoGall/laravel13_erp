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

it('CRUD de tipos de jornada de la company en sesión con auditoría', function () {
    $company = Company::factory()->create();
    $user = actingHrUser($company);

    $this->post(route('rrhh.day-types.store'), [
        'name' => 'Festivo',
        'color' => '#ef4444',
        'is_working' => false,
    ])->assertRedirect();

    $type = DayType::query()->where('name', 'Festivo')->firstOrFail();

    expect($type->company_id)->toBe($company->id)
        ->and($type->color)->toBe('#ef4444')
        ->and($type->is_working)->toBeFalse()
        ->and($type->created_by)->toBe($user->id)
        ->and($type->updated_by)->toBe($user->id);

    $this->put(route('rrhh.day-types.update', $type), [
        'name' => 'Festivo nacional',
        'color' => '#dc2626',
        'is_working' => false,
    ])->assertRedirect();

    $type->refresh();

    expect($type->name)->toBe('Festivo nacional')
        ->and($type->color)->toBe('#dc2626')
        ->and($type->updated_by)->toBe($user->id);

    $this->get(route('rrhh.day-types.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Festivo nacional');

    $this->delete(route('rrhh.day-types.destroy', $type))
        ->assertRedirect(route('rrhh.day-types.index'));

    expect(DayType::query()->whereKey($type->id)->exists())->toBeFalse()
        ->and(DayType::withTrashed()->whereKey($type->id)->exists())->toBeTrue();
});

it('Index y Create renderizan las páginas Inertia de tipos de jornada', function () {
    actingHrUser(Company::factory()->create());

    $this->get(route('rrhh.day-types.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('HR/DayTypes/Index'));

    $this->get(route('rrhh.day-types.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('HR/DayTypes/Create'));
});

it('aísla tipos de jornada entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $typeA = DayType::factory()->create(['company_id' => $companyA->id, 'name' => 'Tipo A']);
    $typeB = DayType::factory()->create(['company_id' => $companyB->id, 'name' => 'Tipo B']);

    actingHrUser($companyA);

    expect(DayType::query()->pluck('id')->all())
        ->toContain($typeA->id)
        ->not->toContain($typeB->id);

    $this->get(route('rrhh.day-types.table'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Tipo A');

    $this->get(route('rrhh.day-types.edit', $typeB))->assertNotFound();
    $this->put(route('rrhh.day-types.update', $typeB), [
        'name' => 'Hacked',
        'color' => '#000000',
        'is_working' => true,
    ])->assertNotFound();
    $this->delete(route('rrhh.day-types.destroy', $typeB))->assertNotFound();

    expect($typeB->fresh()->name)->toBe('Tipo B');
});

it('responde 403 sin permiso de day-types', function () {
    $company = Company::factory()->create();
    $type = DayType::factory()->create(['company_id' => $company->id]);

    actingHrUser($company, ['rrhh.access']);

    $this->get(route('rrhh.day-types.index'))->assertForbidden();
    $this->get(route('rrhh.day-types.table'))->assertForbidden();
    $this->get(route('rrhh.day-types.create'))->assertForbidden();
    $this->post(route('rrhh.day-types.store'), [
        'name' => 'X',
        'color' => '#ffffff',
        'is_working' => true,
    ])->assertForbidden();
    $this->get(route('rrhh.day-types.edit', $type))->assertForbidden();
    $this->put(route('rrhh.day-types.update', $type), [
        'name' => 'X',
        'color' => '#ffffff',
        'is_working' => true,
    ])->assertForbidden();
    $this->delete(route('rrhh.day-types.destroy', $type))->assertForbidden();
});

it('rechaza borrar DayType usado en calendarios laborales', function () {
    $company = Company::factory()->create();
    $workplace = Workplace::factory()->create(['company_id' => $company->id]);
    $type = DayType::factory()->create(['company_id' => $company->id]);
    actingHrUser($company);

    $calendar = WorkCalendar::factory()->forWorkplace($workplace)->create();
    WorkCalendarDay::factory()->create([
        'work_calendar_id' => $calendar->id,
        'date' => '2026-01-01',
        'name' => 'Año Nuevo',
        'day_type_id' => $type->id,
    ]);

    $this->from(route('rrhh.day-types.index'))
        ->delete(route('rrhh.day-types.destroy', $type))
        ->assertRedirect(route('rrhh.day-types.index'))
        ->assertSessionHasErrors('day_type');

    expect(DayType::query()->whereKey($type->id)->exists())->toBeTrue();
});

it('incluye Tipos de jornada en el menú RRHH (V-08)', function () {
    $company = Company::factory()->create();
    $user = actingHrUser($company);

    $rrhh = collect(app(MenuBuilder::class)->forUser($user))
        ->firstWhere('slug', 'rrhh');
    $items = collect($rrhh['functionalities'] ?? [])->keyBy('slug');

    expect($items->has('day-types'))->toBeTrue()
        ->and($items['day-types']['href'])->toBe(route('rrhh.day-types.index', absolute: false));
});
