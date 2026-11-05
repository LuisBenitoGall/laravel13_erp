<?php

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\CurrenciesSeeder;
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
});

it('Super Admin abre Index de monedas', function () {
    $this->get(route('admin.currencies.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Currencies/Index'));
});

it('Index monedas sin columna Estado y con Acciones toggle, Edit y Delete', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Currencies/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->toContain('persistKey="admin.currencies"');
});

it('Index GET no crea filas (sin import lateral)', function () {
    expect(Currency::withTrashed()->count())->toBe(0);

    $this->get(route('admin.currencies.index'))->assertOk();
    $this->getJson(route('admin.currencies.table'))->assertOk();

    expect(Currency::withTrashed()->count())->toBe(0);
});

it('sin currencies.* las rutas de monedas responden 403', function () {
    $currency = Currency::factory()->create();

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.currencies.index'))->assertForbidden();
    $this->getJson(route('admin.currencies.table'))->assertForbidden();
    $this->post(route('admin.currencies.store'), ['name' => 'Nueva'])->assertForbidden();
    $this->put(route('admin.currencies.update', $currency), ['name' => 'X'])->assertForbidden();
    $this->patch(route('admin.currencies.status', $currency))->assertForbidden();
    $this->delete(route('admin.currencies.destroy', $currency))->assertForbidden();

    expect(Currency::query()->count())->toBe(1);
});

it('usuario con currencies.viewAny abre el Index', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $user->givePermissionTo(['configuracion.access', 'currencies.viewAny']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.currencies.index'))->assertOk();
});

it('seeder deja la Functionality currencies con permisos currencies.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "currencies.{$ability}")->exists())->toBeTrue();
    }
});

it('crea moneda con slug, status Active por defecto y valida unicidad de code y number', function () {
    $this->post(route('admin.currencies.store'), [
        'name' => 'Peso Narnia',
        'code' => 'NRN',
        'number' => '9990',
        'symbol' => 'N$',
    ])->assertRedirect();

    $currency = Currency::query()->where('name', 'Peso Narnia')->firstOrFail();

    expect($currency->slug)->toBe('peso-narnia')
        ->and($currency->status)->toBe(CurrencyStatus::Active)
        ->and($currency->code)->toBe('NRN')
        ->and($currency->number)->toBe('9990')
        ->and($currency->symbol)->toBe('N$');

    $this->post(route('admin.currencies.store'), ['name' => 'Otra', 'code' => 'NRN'])
        ->assertSessionHasErrors('code');

    $this->post(route('admin.currencies.store'), ['name' => 'Otra', 'number' => '9990'])
        ->assertSessionHasErrors('number');

    $this->post(route('admin.currencies.store'), ['name' => 'Larga', 'code' => 'ABCD'])
        ->assertSessionHasErrors('code');

    $this->post(route('admin.currencies.store'), ['name' => 'Larga', 'number' => '12345'])
        ->assertSessionHasErrors('number');

    $this->post(route('admin.currencies.store'), ['name' => 'Larga', 'symbol' => 'toolongsym'])
        ->assertSessionHasErrors('symbol');

    $this->post(route('admin.currencies.store'), ['code' => 'ZZZ'])
        ->assertSessionHasErrors('name');
});

it('code y number de una moneda borrada pueden reutilizarse', function () {
    $old = Currency::factory()->create(['code' => 'OLD', 'number' => '1234']);
    $old->delete();

    $this->post(route('admin.currencies.store'), [
        'name' => 'Nueva',
        'code' => 'OLD',
        'number' => '1234',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Currency::query()->where('code', 'OLD')->count())->toBe(1);
});

it('update cambia name, slug y symbol pero nunca code ni number aunque se envíen', function () {
    $currency = Currency::factory()->create([
        'name' => 'Euro Viejo',
        'slug' => 'euro-viejo',
        'code' => 'EUV',
        'number' => '7777',
        'symbol' => null,
    ]);

    $this->put(route('admin.currencies.update', $currency), [
        'name' => 'Euro Nuevo',
        'symbol' => '€',
        'code' => 'XXX',
        'number' => '0001',
    ])->assertRedirect();

    $currency->refresh();

    expect($currency->name)->toBe('Euro Nuevo')
        ->and($currency->slug)->toBe('euro-nuevo')
        ->and($currency->symbol)->toBe('€')
        ->and($currency->code)->toBe('EUV')
        ->and($currency->number)->toBe('7777')
        ->and($currency->status)->toBe(CurrencyStatus::Active);

    $this->put(route('admin.currencies.update', $currency), ['name' => ''])
        ->assertSessionHasErrors('name');

    $this->put(route('admin.currencies.update', $currency), ['name' => 'Euro Nuevo', 'symbol' => 'toolongsym'])
        ->assertSessionHasErrors('symbol');
});

it('update mantiene el slug si el name no cambia y lo hace único si cambia a uno ocupado', function () {
    $first = Currency::factory()->create(['name' => 'Alfa', 'slug' => 'alfa']);
    $second = Currency::factory()->create(['name' => 'Beta', 'slug' => 'beta']);

    $this->put(route('admin.currencies.update', $first), ['name' => 'Alfa', 'symbol' => 'A'])
        ->assertRedirect();

    expect($first->refresh()->slug)->toBe('alfa');

    $this->put(route('admin.currencies.update', $second), ['name' => 'Alfa'])
        ->assertRedirect();

    expect($second->refresh()->slug)->not->toBe('alfa')
        ->and($second->slug)->toStartWith('alfa');
});

it('toggle status alterna Active/Inactive y acepta status explícito', function () {
    $currency = Currency::factory()->create();

    $this->patchJson(route('admin.currencies.status', $currency))
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    $this->patchJson(route('admin.currencies.status', $currency))
        ->assertOk()
        ->assertJson(['status' => 'active']);

    $this->patchJson(route('admin.currencies.status', $currency), ['status' => 'inactive'])
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    expect($currency->refresh()->status)->toBe(CurrencyStatus::Inactive);
});

it('borra moneda con soft delete', function () {
    $currency = Currency::factory()->create();

    $this->delete(route('admin.currencies.destroy', $currency))
        ->assertRedirect(route('admin.currencies.index'));

    expect(Currency::query()->whereKey($currency->id)->exists())->toBeFalse()
        ->and(Currency::withTrashed()->whereKey($currency->id)->exists())->toBeTrue();
});

it('table de monedas filtra por search y expone number y symbol', function () {
    Currency::factory()->create(['name' => 'Zzmoneda', 'code' => 'ZZM', 'number' => '4321', 'symbol' => 'Z']);
    Currency::factory()->create(['name' => 'Otra', 'code' => 'OTR', 'number' => '1111']);

    $rows = collect($this->getJson(route('admin.currencies.table', [
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Zzm']],
    ]))->assertOk()->json('data'));

    expect($rows)->toHaveCount(1)
        ->and($rows->first()['number'])->toBe('4321')
        ->and($rows->first()['symbol'])->toBe('Z')
        ->and($rows->first()['status'])->toBe('active');
});

it('monedas es catálogo global sin company_id y visible desde dos companies (R-TEN-05 sustituto)', function () {
    expect(Schema::hasColumn('currencies', 'company_id'))->toBeFalse();

    $currency = Currency::factory()->create(['name' => 'Atlantis Dollar']);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $names = fn () => collect($this->getJson(route('admin.currencies.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis Dollar');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis Dollar')
        ->and(Currency::query()->whereKey($currency->id)->exists())->toBeTrue();
});

it('CurrenciesSeeder no falla y, si hay JSON v1, preserva ids y deja habituales Active', function () {
    $this->seed(CurrenciesSeeder::class);

    $path = database_path('data/currencies/currencies.json');

    if (! is_file($path)) {
        expect(Currency::withTrashed()->count())->toBe(0);

        return;
    }

    /** @var list<array<string, mixed>> $rows */
    $rows = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    expect(Currency::withTrashed()->count())->toBe(count($rows))
        ->and(Currency::query()->whereKey(2)->value('code'))->toBe('EUR')
        ->and(Currency::query()->whereKey(2)->first()->status)->toBe(CurrencyStatus::Active);

    foreach (['EUR', 'USD', 'GBP', 'CHF'] as $code) {
        expect(Currency::query()->where('code', $code)->first()?->status)->toBe(CurrencyStatus::Active);
    }

    expect(Currency::query()->where('status', CurrencyStatus::Active)->count())->toBe(4)
        ->and(Currency::query()->where('code', 'EUR')->value('symbol'))->toBe('€');

    $this->seed(CurrenciesSeeder::class);

    expect(Currency::withTrashed()->count())->toBe(count($rows));
});
