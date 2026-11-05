<?php

use App\Domains\Admin\Enums\VatTypeStatus;
use App\Domains\Admin\Models\Country;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\VatType;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\VatTypesSeeder;
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

    $this->country = Country::factory()->create(['name' => 'España Test']);
    $this->otherCountry = Country::factory()->create(['name' => 'Francia Test']);

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

it('Super Admin abre Index de tipos de IVA', function () {
    $this->get(route('admin.vat-types.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/VatTypes/Index')
            ->has('countries'));
});

it('Index incluye Country Inactive en el selector de países', function () {
    $inactive = Country::factory()->inactive()->create([
        'name' => 'País Inactive VatSelect',
    ]);

    $this->get(route('admin.vat-types.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/VatTypes/Index')
            ->has('countries')
            ->where('countries', fn ($countries) => collect($countries)->contains(
                fn ($country) => (int) $country['id'] === $inactive->id
                    && $country['status'] === 'inactive'
            )));
});

it('Index tipos IVA sin columna Estado y con Acciones toggle, Edit y Delete', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/VatTypes/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->toContain('persistKey="admin.vat-types"')
        ->and($source)->toContain("field: 'country_id'");
});

it('Index GET no crea filas (sin import lateral)', function () {
    expect(VatType::withTrashed()->count())->toBe(0);

    $this->get(route('admin.vat-types.index'))->assertOk();
    $this->getJson(route('admin.vat-types.table'))->assertOk();

    expect(VatType::withTrashed()->count())->toBe(0);
});

it('sin vat-types.* las rutas responden 403', function () {
    $vatType = VatType::factory()->create(['country_id' => $this->country->id]);

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.vat-types.index'))->assertForbidden();
    $this->getJson(route('admin.vat-types.table'))->assertForbidden();
    $this->post(route('admin.vat-types.store'), [
        'country_id' => $this->country->id,
        'name' => 'Nuevo',
        'rate' => 21,
    ])->assertForbidden();
    $this->put(route('admin.vat-types.update', $vatType), [
        'country_id' => $this->country->id,
        'name' => 'X',
        'rate' => 10,
    ])->assertForbidden();
    $this->patch(route('admin.vat-types.status', $vatType))->assertForbidden();
    $this->delete(route('admin.vat-types.destroy', $vatType))->assertForbidden();

    expect(VatType::query()->count())->toBe(1);
});

it('usuario con vat-types.viewAny abre el Index', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $user->givePermissionTo(['configuracion.access', 'vat-types.viewAny']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.vat-types.index'))->assertOk();
});

it('seeder deja la Functionality vat-types con permisos vat-types.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "vat-types.{$ability}")->exists())->toBeTrue();
    }
});

it('crea tipo IVA con country required, unique name por país y RE nullable', function () {
    $this->post(route('admin.vat-types.store'), [
        'name' => 'General',
        'rate' => 21,
    ])->assertSessionHasErrors('country_id');

    $this->post(route('admin.vat-types.store'), [
        'country_id' => $this->country->id,
        'name' => 'General',
        'rate' => 21,
        'equivalence_surcharge' => 5.2,
    ])->assertRedirect();

    $vatType = VatType::query()->where('name', 'General')->firstOrFail();

    expect($vatType->country_id)->toBe($this->country->id)
        ->and($vatType->status)->toBe(VatTypeStatus::Active)
        ->and((float) $vatType->rate)->toBe(21.0)
        ->and((float) $vatType->equivalence_surcharge)->toBe(5.2);

    $this->post(route('admin.vat-types.store'), [
        'country_id' => $this->country->id,
        'name' => 'General',
        'rate' => 10,
    ])->assertSessionHasErrors('name');

    $this->post(route('admin.vat-types.store'), [
        'country_id' => $this->otherCountry->id,
        'name' => 'General',
        'rate' => 20,
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->post(route('admin.vat-types.store'), [
        'country_id' => $this->country->id,
        'name' => 'Sin RE',
        'rate' => 0,
        'equivalence_surcharge' => '',
    ])->assertRedirect();

    expect(VatType::query()->where('name', 'Sin RE')->value('equivalence_surcharge'))->toBeNull();
});

it('update cambia país, name, rate y RE', function () {
    $vatType = VatType::factory()->create([
        'country_id' => $this->country->id,
        'name' => 'Viejo',
        'rate' => '10.00',
    ]);

    $this->put(route('admin.vat-types.update', $vatType), [
        'country_id' => $this->otherCountry->id,
        'name' => 'Nuevo',
        'rate' => 21,
        'equivalence_surcharge' => 5.2,
    ])->assertRedirect();

    $vatType->refresh();

    expect($vatType->country_id)->toBe($this->otherCountry->id)
        ->and($vatType->name)->toBe('Nuevo')
        ->and((float) $vatType->rate)->toBe(21.0)
        ->and((float) $vatType->equivalence_surcharge)->toBe(5.2);
});

it('toggle status alterna Active/Inactive', function () {
    $vatType = VatType::factory()->create(['country_id' => $this->country->id]);

    $this->patchJson(route('admin.vat-types.status', $vatType))
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    expect($vatType->refresh()->status)->toBe(VatTypeStatus::Inactive);
});

it('borra tipo IVA con soft delete', function () {
    $vatType = VatType::factory()->create(['country_id' => $this->country->id]);

    $this->delete(route('admin.vat-types.destroy', $vatType))
        ->assertRedirect(route('admin.vat-types.index'));

    expect(VatType::query()->whereKey($vatType->id)->exists())->toBeFalse()
        ->and(VatType::withTrashed()->whereKey($vatType->id)->exists())->toBeTrue();
});

it('table sin sorts aplica Active-first + name y filtra por país', function () {
    VatType::factory()->create([
        'country_id' => $this->country->id,
        'name' => 'Zzz Vatsort Active',
        'status' => VatTypeStatus::Active,
    ]);
    VatType::factory()->inactive()->create([
        'country_id' => $this->country->id,
        'name' => 'Aaa Vatsort Inactive',
    ]);
    VatType::factory()->create([
        'country_id' => $this->otherCountry->id,
        'name' => 'Mmm Vatsort Other',
        'status' => VatTypeStatus::Active,
    ]);

    $rows = collect($this->getJson(route('admin.vat-types.table', [
        'size' => 200,
        'filter' => [
            ['field' => 'search', 'type' => 'like', 'value' => 'Vatsort'],
            ['field' => 'country_id', 'type' => '=', 'value' => $this->country->id],
        ],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Zzz Vatsort Active',
        'Aaa Vatsort Inactive',
    ])->and($rows->first()['country']['name'])->toBe('España Test');
});

it('table con sort por name no prefija Active-first', function () {
    VatType::factory()->create([
        'country_id' => $this->country->id,
        'name' => 'Zzz Vatsort Active',
        'status' => VatTypeStatus::Active,
    ]);
    VatType::factory()->inactive()->create([
        'country_id' => $this->country->id,
        'name' => 'Aaa Vatsort Inactive',
    ]);

    $rows = collect($this->getJson(route('admin.vat-types.table', [
        'size' => 200,
        'filter' => [
            ['field' => 'search', 'type' => 'like', 'value' => 'Vatsort'],
            ['field' => 'country_id', 'type' => '=', 'value' => $this->country->id],
        ],
        'sort' => [['field' => 'name', 'dir' => 'asc']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Aaa Vatsort Inactive',
        'Zzz Vatsort Active',
    ]);
});

it('tipos IVA es catálogo global sin company_id y visible desde dos companies', function () {
    expect(Schema::hasColumn('vat_types', 'company_id'))->toBeFalse();

    $vatType = VatType::factory()->create([
        'country_id' => $this->country->id,
        'name' => 'Atlantis VAT',
    ]);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $names = fn () => collect($this->getJson(route('admin.vat-types.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis VAT');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis VAT')
        ->and(VatType::query()->whereKey($vatType->id)->exists())->toBeTrue();
});

it('VatTypesSeeder falla sin España y, con país 66, preserva ids', function () {
    expect(fn () => $this->seed(VatTypesSeeder::class))
        ->toThrow(RuntimeException::class);

    Country::factory()->create([
        'id' => VatTypesSeeder::SPAIN_COUNTRY_ID,
        'name' => 'España',
        'code' => 'ES',
    ]);

    $this->seed(VatTypesSeeder::class);

    $path = database_path('data/vat-types/iva-types.json');

    if (! is_file($path)) {
        return;
    }

    /** @var list<array<string, mixed>> $rows */
    $rows = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    expect(VatType::withTrashed()->count())->toBe(count($rows))
        ->and(VatType::query()->whereKey(2)->value('name'))->toBe('General')
        ->and(VatType::query()->whereKey(2)->value('country_id'))->toBe(66)
        ->and((float) VatType::query()->whereKey(2)->value('rate'))->toBe(21.0)
        ->and(VatType::query()->whereKey(2)->first()?->status)->toBe(VatTypeStatus::Active);

    $this->seed(VatTypesSeeder::class);

    expect(VatType::withTrashed()->count())->toBe(count($rows));
});

it('no se puede borrar un país con tipos de IVA', function () {
    VatType::factory()->create(['country_id' => $this->country->id]);

    $this->delete(route('admin.countries.destroy', $this->country))
        ->assertRedirect()
        ->assertSessionHasErrors('country');

    expect(Country::query()->whereKey($this->country->id)->exists())->toBeTrue();
});
