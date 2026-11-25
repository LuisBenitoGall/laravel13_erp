<?php

use App\Domains\Admin\Actions\ResolveExchangeRate;
use App\Domains\Admin\Actions\UpsertExchangeRatePair;
use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\ExchangeRate;
use App\Domains\Admin\Models\Role;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    enableModuleForCompany($this->company, 'configuracion');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);

    // Dos monedas Active para los tests
    $this->eur = Currency::factory()->create(['code' => 'EUR', 'status' => CurrencyStatus::Active]);
    $this->usd = Currency::factory()->create(['code' => 'USD', 'status' => CurrencyStatus::Active]);
});

// ─── Task 2.3 — Global catalog cross-company ───────────────────────────────

it('ExchangeRate es catálogo global sin company_id y visible desde dos companies', function () {
    expect(Schema::hasColumn('exchange_rates', 'company_id'))->toBeFalse();

    $pair = ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.100000',
        'rate_date' => '2026-11-01',
    ]);

    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    // Desde la company original
    $rows = collect($this->getJson(route('admin.exchange-rates.table'))->assertOk()->json('data'));
    expect($rows->pluck('id'))->toContain($pair->id);

    // Desde otra company
    actingWithCompany($this->user, $otherCompany);
    $rows2 = collect($this->getJson(route('admin.exchange-rates.table'))->assertOk()->json('data'));
    expect($rows2->pluck('id'))->toContain($pair->id);

    // La fila existe en BD sin company_id
    expect(ExchangeRate::query()->whereKey($pair->id)->exists())->toBeTrue();
});

// ─── Task 3.4 — Auth 403 ──────────────────────────────────────────────────

it('sin exchange-rates.* las rutas responden 403', function () {
    $rate = ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.100000',
        'rate_date' => '2026-11-01',
    ]);

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.exchange-rates.index'))->assertForbidden();
    $this->getJson(route('admin.exchange-rates.table'))->assertForbidden();
    $this->post(route('admin.exchange-rates.store'), [])->assertForbidden();
    $this->put(route('admin.exchange-rates.update', $rate), [])->assertForbidden();
    $this->delete(route('admin.exchange-rates.destroy', $rate))->assertForbidden();

    expect(ExchangeRate::query()->count())->toBe(1);
});

it('usuario SA con exchange-rates.view abre el Index', function () {
    // configuracion es AdminOnly: solo el SA pasa el middleware EnsureModuleIsActive.
    // El SA hereda todos los permisos vía Gate::before, por lo que assertOk() es correcto.
    $this->get(route('admin.exchange-rates.index'))->assertOk();
});

// ─── Task 3.4 — Create dual → 2 filas ────────────────────────────────────

it('create dual → crea 2 filas dirigidas', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-01',
        'rate_ab' => '1.100000',
        'rate_ba' => '0.909091',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $ab = ExchangeRate::query()
        ->where('from_currency_id', $this->eur->id)
        ->where('to_currency_id', $this->usd->id)
        ->whereDate('rate_date', '2026-11-01')
        ->first();

    $ba = ExchangeRate::query()
        ->where('from_currency_id', $this->usd->id)
        ->where('to_currency_id', $this->eur->id)
        ->whereDate('rate_date', '2026-11-01')
        ->first();

    expect($ab)->not->toBeNull()
        ->and($ba)->not->toBeNull()
        ->and((float) $ab->rate)->toBe(1.1)
        ->and((float) $ba->rate)->toBe(0.909091);
});

// ─── Task 3.4 — Create un sentido ────────────────────────────────────────

it('create un solo sentido → solo crea 1 fila', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-02',
        'rate_ab' => '1.050000',
        'rate_ba' => '',
    ])->assertRedirect();

    expect(ExchangeRate::query()->count())->toBe(1)
        ->and(ExchangeRate::query()->where('from_currency_id', $this->eur->id)->exists())->toBeTrue()
        ->and(ExchangeRate::query()->where('from_currency_id', $this->usd->id)->exists())->toBeFalse();
});

it('create sin ningún rate falla validación', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-02',
        'rate_ab' => '',
        'rate_ba' => '',
    ])->assertSessionHasErrors('rate_ab');
});

it('create con from === to falla validación', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->eur->id,
        'rate_date' => '2026-11-02',
        'rate_ab' => '1.0',
        'rate_ba' => '',
    ])->assertSessionHasErrors('to_currency_id');
});

it('create con rate ≤ 0 falla validación', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-02',
        'rate_ab' => '0',
        'rate_ba' => '',
    ])->assertSessionHasErrors('rate_ab');

    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-02',
        'rate_ab' => '-1.5',
        'rate_ba' => '',
    ])->assertSessionHasErrors('rate_ab');
});

it('table agrupa por par+fecha con rate_ab y rate_ba', function () {
    ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.100000',
        'rate_date' => '2026-11-01',
    ]);
    ExchangeRate::factory()->create([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'rate' => '0.909091',
        'rate_date' => '2026-11-01',
    ]);

    $rows = collect($this->getJson(route('admin.exchange-rates.table'))->assertOk()->json('data'));

    expect($rows)->toHaveCount(1);

    $row = $rows->first();
    $aId = min($this->eur->id, $this->usd->id);
    $bId = max($this->eur->id, $this->usd->id);
    $rateAb = $aId === $this->eur->id ? 1.1 : 0.909091;
    $rateBa = $aId === $this->eur->id ? 0.909091 : 1.1;

    expect($row['currency_a_id'])->toBe($aId)
        ->and($row['currency_b_id'])->toBe($bId)
        ->and((float) $row['rate_ab'])->toBe($rateAb)
        ->and((float) $row['rate_ba'])->toBe($rateBa)
        ->and($row['pair_label'])->toContain('EUR')
        ->and($row['pair_label'])->toContain('USD');
});

// ─── Task 3.4 — Upsert same day ──────────────────────────────────────────

it('upsert same day actualiza el rate existente en lugar de duplicar', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-01',
        'rate_ab' => '1.100000',
        'rate_ba' => '',
    ])->assertRedirect();

    expect(ExchangeRate::query()->count())->toBe(1);

    // Mismo par+fecha → upsert
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-01',
        'rate_ab' => '1.150000',
        'rate_ba' => '',
    ])->assertRedirect();

    expect(ExchangeRate::query()->count())->toBe(1);
    expect((float) ExchangeRate::query()->first()->rate)->toBe(1.15);
});

it('upsert restaura fila soft-deleted del mismo par+fecha', function () {
    $existing = ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.100000',
        'rate_date' => '2026-11-01',
    ]);
    $existing->delete();

    expect(ExchangeRate::query()->count())->toBe(0)
        ->and(ExchangeRate::withTrashed()->count())->toBe(1);

    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-01',
        'rate_ab' => '1.200000',
        'rate_ba' => '',
    ])->assertRedirect();

    expect(ExchangeRate::query()->count())->toBe(1)
        ->and((float) ExchangeRate::query()->first()->rate)->toBe(1.2);
});

// ─── Task 3.4 — Soft delete ──────────────────────────────────────────────

it('delete soft-deletea ambos sentidos del par+fecha', function () {
    app(UpsertExchangeRatePair::class)->handle([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-01',
        'rate_ab' => '1.1',
        'rate_ba' => '0.909091',
    ]);

    $ab = ExchangeRate::query()
        ->where('from_currency_id', $this->eur->id)
        ->where('to_currency_id', $this->usd->id)
        ->first();

    $this->delete(route('admin.exchange-rates.destroy', $ab))
        ->assertRedirect(route('admin.exchange-rates.index'));

    expect(ExchangeRate::query()->count())->toBe(0)
        ->and(ExchangeRate::withTrashed()->count())->toBe(2)
        ->and(ExchangeRate::withTrashed()->where('from_currency_id', $this->eur->id)->exists())->toBeTrue()
        ->and(ExchangeRate::withTrashed()->where('from_currency_id', $this->usd->id)->exists())->toBeTrue();
});

// ─── Task 3.4 — ResolveExchangeRate D−3 / D / null ───────────────────────

it('ResolveExchangeRate devuelve mayor rate_date <= D', function () {
    $resolve = app(ResolveExchangeRate::class);

    // Sin cotizaciones → null
    expect($resolve->handle($this->eur->id, $this->usd->id, '2026-11-01'))->toBeNull();

    // Cotización en D−3
    ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.050000',
        'rate_date' => '2026-10-29',
    ]);

    // Consultar D → devuelve la de D−3 (carry-forward)
    $result = $resolve->handle($this->eur->id, $this->usd->id, '2026-11-01');
    expect($result)->not->toBeNull()
        ->and($result->rate_date->format('Y-m-d'))->toBe('2026-10-29');

    // Cotización exactamente en D
    ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.100000',
        'rate_date' => '2026-11-01',
    ]);

    $result = $resolve->handle($this->eur->id, $this->usd->id, '2026-11-01');
    expect($result->rate_date->format('Y-m-d'))->toBe('2026-11-01');

    // Consultar antes de cualquier cotización → null
    expect($resolve->handle($this->eur->id, $this->usd->id, '2026-10-28'))->toBeNull();
});

it('ResolveExchangeRate NO invierte el par (D8)', function () {
    $resolve = app(ResolveExchangeRate::class);

    // Solo hay USD→EUR, no EUR→USD
    ExchangeRate::factory()->create([
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'rate' => '0.909091',
        'rate_date' => '2026-11-01',
    ]);

    expect($resolve->handle($this->eur->id, $this->usd->id, '2026-11-01'))->toBeNull();
});

// ─── Task 3.4 — Default sort rate_date desc ──────────────────────────────

it('Index default sort: rate_date desc', function () {
    ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.0',
        'rate_date' => '2026-10-01',
    ]);
    ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.1',
        'rate_date' => '2026-11-01',
    ]);

    $rows = $this->getJson(route('admin.exchange-rates.table'))->assertOk()->json('data');

    expect($rows[0]['rate_date'])->toBe('2026-11-01')
        ->and($rows[1]['rate_date'])->toBe('2026-10-01');
});

// ─── Task 5.3 — V-21: sin permiso → 403 ─────────────────────────────────

it('V-21: Policy viewAny mapea a exchange-rates.view', function () {
    $rate = ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.0',
        'rate_date' => '2026-11-01',
    ]);
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('exchange-rates.view');
    Permission::findOrCreate('exchange-rates.viewAny');
    $user->givePermissionTo(['exchange-rates.view']);
    actingWithCompany($user, $this->company);

    expect($user->can('viewAny', ExchangeRate::class))->toBeTrue()
        ->and($user->can('view', $rate))->toBeFalse();

    $user->givePermissionTo('exchange-rates.viewAny');
    $user->unsetRelation('permissions');
    $user->unsetRelation('roles');

    expect($user->can('view', $rate))->toBeTrue();
});

it('seeder deja la Functionality exchange-rates con permisos exchange-rates.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "exchange-rates.{$ability}")->exists())->toBeTrue();
    }
});

// ─── Task 5.4 — V-20: Auditable ──────────────────────────────────────────

it('V-20: Auditable rellena created_by y updated_by', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_date' => '2026-11-01',
        'rate_ab' => '1.1',
        'rate_ba' => '',
    ])->assertRedirect();

    $rate = ExchangeRate::query()->first();

    expect($rate->created_by)->toBe($this->user->id)
        ->and($rate->updated_by)->toBe($this->user->id);
});

// ─── Task 5.2 — Menú SA incluye Conversión monedas ───────────────────────

it('menú SA incluye Conversión monedas (V-07, V-08)', function () {
    $response = $this->get(route('admin.exchange-rates.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/ExchangeRates/Index'));
});

it('Index GET no crea filas', function () {
    expect(ExchangeRate::withTrashed()->count())->toBe(0);

    $this->get(route('admin.exchange-rates.index'))->assertOk();
    $this->getJson(route('admin.exchange-rates.table'))->assertOk();

    expect(ExchangeRate::withTrashed()->count())->toBe(0);
});
