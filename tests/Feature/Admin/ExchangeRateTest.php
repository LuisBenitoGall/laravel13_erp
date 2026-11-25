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

    $this->eur = Currency::factory()->create(['code' => 'EUR', 'status' => CurrencyStatus::Active]);
    $this->usd = Currency::factory()->create(['code' => 'USD', 'status' => CurrencyStatus::Active]);
});

it('ExchangeRate es catálogo global sin company_id ni rate_date', function () {
    expect(Schema::hasColumn('exchange_rates', 'company_id'))->toBeFalse()
        ->and(Schema::hasColumn('exchange_rates', 'rate_date'))->toBeFalse();

    $pair = ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.100000',
    ]);

    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $rows = collect($this->getJson(route('admin.exchange-rates.table'))->assertOk()->json('data'));
    expect($rows->pluck('id'))->toContain($pair->id);

    actingWithCompany($this->user, $otherCompany);
    $rows2 = collect($this->getJson(route('admin.exchange-rates.table'))->assertOk()->json('data'));
    expect($rows2->pluck('id'))->toContain($pair->id);
});

it('sin exchange-rates.* las rutas responden 403', function () {
    $rate = ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.100000',
    ]);

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.exchange-rates.index'))->assertForbidden();
    $this->getJson(route('admin.exchange-rates.table'))->assertForbidden();
    $this->post(route('admin.exchange-rates.store'), [])->assertForbidden();
    $this->put(route('admin.exchange-rates.update', $rate), [])->assertForbidden();
    $this->delete(route('admin.exchange-rates.destroy', $rate))->assertForbidden();

    expect(ExchangeRate::query()->count())->toBe(1);
});

it('usuario SA abre el Index', function () {
    $this->get(route('admin.exchange-rates.index'))->assertOk();
});

it('create dual → crea 2 filas dirigidas y 1 fila de par en Index', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_ab' => '1.100000',
        'rate_ba' => '0.909091',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(ExchangeRate::query()->count())->toBe(2);

    $rows = collect($this->getJson(route('admin.exchange-rates.table'))->assertOk()->json('data'));
    expect($rows)->toHaveCount(1)
        ->and($rows->first()['pair_label'])->toContain('EUR')
        ->and($rows->first()['pair_label'])->toContain('USD')
        ->and($rows->first())->not->toHaveKey('rate_date');
});

it('create un solo sentido → solo crea 1 fila y ocupa el par', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_ab' => '1.050000',
        'rate_ba' => '',
    ])->assertRedirect();

    expect(ExchangeRate::query()->count())->toBe(1);

    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'rate_ab' => '0.95',
        'rate_ba' => '',
    ])->assertSessionHasErrors('from_currency_id');
});

it('create sin ningún rate falla validación', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_ab' => '',
        'rate_ba' => '',
    ])->assertSessionHasErrors('rate_ab');
});

it('create con from === to falla validación', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->eur->id,
        'rate_ab' => '1.0',
        'rate_ba' => '',
    ])->assertSessionHasErrors('to_currency_id');
});

it('Create rechaza par unordered duplicado', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_ab' => '1.100000',
        'rate_ba' => '',
    ])->assertRedirect();

    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->usd->id,
        'to_currency_id' => $this->eur->id,
        'rate_ab' => '0.900000',
        'rate_ba' => '',
    ])->assertSessionHasErrors('from_currency_id');

    expect(ExchangeRate::query()->count())->toBe(1);
});

it('Edit actualiza rates in-place sin duplicar', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_ab' => '1.100000',
        'rate_ba' => '0.909091',
    ])->assertRedirect();

    $row = ExchangeRate::query()->firstOrFail();

    $this->put(route('admin.exchange-rates.update', $row), [
        'rate_ab' => '1.200000',
        'rate_ba' => '0.833333',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(ExchangeRate::query()->count())->toBe(2);

    $aId = min($this->eur->id, $this->usd->id);
    $bId = max($this->eur->id, $this->usd->id);
    $ab = ExchangeRate::query()->where('from_currency_id', $aId)->where('to_currency_id', $bId)->first();
    $ba = ExchangeRate::query()->where('from_currency_id', $bId)->where('to_currency_id', $aId)->first();

    expect((float) $ab->rate)->toBe(1.2)
        ->and((float) $ba->rate)->toBe(0.833333);
});

it('delete soft-deletea ambos sentidos del par', function () {
    app(UpsertExchangeRatePair::class)->handle([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_ab' => '1.1',
        'rate_ba' => '0.909091',
    ]);

    $ab = ExchangeRate::query()
        ->where('from_currency_id', $this->eur->id)
        ->where('to_currency_id', $this->usd->id)
        ->firstOrFail();

    $this->delete(route('admin.exchange-rates.destroy', $ab))
        ->assertRedirect(route('admin.exchange-rates.index'));

    expect(ExchangeRate::query()->count())->toBe(0)
        ->and(ExchangeRate::withTrashed()->count())->toBe(2);
});

it('ResolveExchangeRate devuelve la fila actual o null', function () {
    $resolve = app(ResolveExchangeRate::class);

    expect($resolve->handle($this->eur->id, $this->usd->id))->toBeNull();

    ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.050000',
    ]);

    $result = $resolve->handle($this->eur->id, $this->usd->id);
    expect($result)->not->toBeNull()
        ->and((float) $result->rate)->toBe(1.05);

    // No inventa el inverso
    expect($resolve->handle($this->usd->id, $this->eur->id))->toBeNull();
});

it('V-21: Policy viewAny mapea a exchange-rates.view', function () {
    $rate = ExchangeRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate' => '1.0',
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

it('V-20: Auditable rellena created_by y updated_by', function () {
    $this->post(route('admin.exchange-rates.store'), [
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'rate_ab' => '1.1',
        'rate_ba' => '',
    ])->assertRedirect();

    $rate = ExchangeRate::query()->first();

    expect($rate->created_by)->toBe($this->user->id)
        ->and($rate->updated_by)->toBe($this->user->id);
});

it('UI Index no usa rate_date', function () {
    $index = file_get_contents(resource_path('js/Pages/Admin/ExchangeRates/Index.tsx'));

    expect($index)->toContain('pair_label')
        ->and($index)->toContain('tasa_uno_igual')
        ->and($index)->toContain('rellenar_inverso')
        ->and($index)->not->toContain('rate_date')
        ->and($index)->not->toContain("field: 'fecha'");
});
