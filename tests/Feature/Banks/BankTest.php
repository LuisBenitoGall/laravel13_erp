<?php

use App\Domains\Admin\Models\Role;
use App\Domains\Banks\Enums\BankStatus;
use App\Domains\Banks\Models\Bank;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\BanksSeeder;
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

it('Super Admin abre Index de bancos', function () {
    $this->get(route('admin.banks.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Banks/Banks/Index'));
});

it('Index bancos sin columna Estado y con Acciones toggle, Edit y Delete', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Banks/Banks/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->toContain('persistKey="admin.banks"');
});

it('Index GET no crea filas (sin import lateral)', function () {
    expect(Bank::withTrashed()->count())->toBe(0);

    $this->get(route('admin.banks.index'))->assertOk();
    $this->getJson(route('admin.banks.table'))->assertOk();

    expect(Bank::withTrashed()->count())->toBe(0);
});

it('sin banks.* las rutas de bancos responden 403', function () {
    $bank = Bank::factory()->create();

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.banks.index'))->assertForbidden();
    $this->getJson(route('admin.banks.table'))->assertForbidden();
    $this->post(route('admin.banks.store'), ['name' => 'Nuevo', 'eu_code' => 'ES9999'])->assertForbidden();
    $this->put(route('admin.banks.update', $bank), ['name' => 'X', 'eu_code' => 'ES9998'])->assertForbidden();
    $this->patch(route('admin.banks.status', $bank))->assertForbidden();
    $this->delete(route('admin.banks.destroy', $bank))->assertForbidden();

    expect(Bank::query()->count())->toBe(1);
});

it('usuario con banks.viewAny abre el Index', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $user->givePermissionTo(['configuracion.access', 'banks.viewAny']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.banks.index'))->assertOk();
});

it('seeder deja la Functionality banks con permisos banks.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "banks.{$ability}")->exists())->toBeTrue();
    }
});

it('crea banco con slug, pad SWIFT, status Active y valida unicidad de eu_code', function () {
    $this->post(route('admin.banks.store'), [
        'name' => 'Banco Narnia',
        'tradename' => 'Narnia Bank',
        'eu_code' => 'ES8801',
        'swift' => 'NARNESMM',
    ])->assertRedirect();

    $bank = Bank::query()->where('name', 'Banco Narnia')->firstOrFail();

    expect($bank->slug)->toBe('banco-narnia')
        ->and($bank->status)->toBe(BankStatus::Active)
        ->and($bank->eu_code)->toBe('ES8801')
        ->and($bank->swift)->toBe('NARNESMMXXX')
        ->and($bank->swift_source)->toBe('manual')
        ->and($bank->swift_updated_at)->not->toBeNull();

    $this->post(route('admin.banks.store'), [
        'name' => 'Otro',
        'eu_code' => 'ES8801',
    ])->assertSessionHasErrors('eu_code');

    $this->post(route('admin.banks.store'), [
        'name' => 'Sin codigo',
    ])->assertSessionHasErrors('eu_code');

    $this->post(route('admin.banks.store'), [
        'eu_code' => 'ES8802',
    ])->assertSessionHasErrors('name');
});

it('eu_code de un banco borrado puede reutilizarse', function () {
    $old = Bank::factory()->create(['eu_code' => 'ES7777']);
    $old->delete();

    $this->post(route('admin.banks.store'), [
        'name' => 'Reusado',
        'eu_code' => 'ES7777',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Bank::query()->where('eu_code', 'ES7777')->count())->toBe(1);
});

it('update cambia campos y regenera slug; marca SWIFT manual al cambiar', function () {
    $bank = Bank::factory()->create([
        'name' => 'Banco Viejo',
        'slug' => 'banco-viejo',
        'eu_code' => 'ES1111',
        'swift' => null,
    ]);

    $this->put(route('admin.banks.update', $bank), [
        'name' => 'Banco Nuevo',
        'tradename' => 'Nuevo',
        'eu_code' => 'ES1111',
        'swift' => 'CAIXESBB',
    ])->assertRedirect();

    $bank->refresh();

    expect($bank->name)->toBe('Banco Nuevo')
        ->and($bank->slug)->toBe('banco-nuevo')
        ->and($bank->swift)->toBe('CAIXESBBXXX')
        ->and($bank->swift_source)->toBe('manual')
        ->and($bank->swift_updated_at)->not->toBeNull();

    $this->put(route('admin.banks.update', $bank), [
        'name' => '',
        'eu_code' => 'ES1111',
    ])->assertSessionHasErrors('name');
});

it('toggle status alterna Active/Inactive y acepta status explícito', function () {
    $bank = Bank::factory()->create();

    $this->patchJson(route('admin.banks.status', $bank))
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    $this->patchJson(route('admin.banks.status', $bank))
        ->assertOk()
        ->assertJson(['status' => 'active']);

    $this->patchJson(route('admin.banks.status', $bank), ['status' => 'inactive'])
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    expect($bank->refresh()->status)->toBe(BankStatus::Inactive);
});

it('borra banco con soft delete', function () {
    $bank = Bank::factory()->create();

    $this->delete(route('admin.banks.destroy', $bank))
        ->assertRedirect(route('admin.banks.index'));

    expect(Bank::query()->whereKey($bank->id)->exists())->toBeFalse()
        ->and(Bank::withTrashed()->whereKey($bank->id)->exists())->toBeTrue();
});

it('table sin sorts aplica Active-first y luego name', function () {
    Bank::factory()->create([
        'name' => 'Zzz Banksort Active',
        'eu_code' => 'ES9001',
        'status' => BankStatus::Active,
    ]);
    Bank::factory()->inactive()->create([
        'name' => 'Aaa Banksort Inactive',
        'eu_code' => 'ES9002',
    ]);
    Bank::factory()->create([
        'name' => 'Mmm Banksort Active',
        'eu_code' => 'ES9003',
        'status' => BankStatus::Active,
    ]);

    $rows = collect($this->getJson(route('admin.banks.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Banksort']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Mmm Banksort Active',
        'Zzz Banksort Active',
        'Aaa Banksort Inactive',
    ]);
});

it('table con sort por name no prefija Active-first', function () {
    Bank::factory()->create([
        'name' => 'Zzz Banksort Active',
        'eu_code' => 'ES9011',
        'status' => BankStatus::Active,
    ]);
    Bank::factory()->inactive()->create([
        'name' => 'Aaa Banksort Inactive',
        'eu_code' => 'ES9012',
    ]);

    $rows = collect($this->getJson(route('admin.banks.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Banksort']],
        'sort' => [['field' => 'name', 'dir' => 'asc']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Aaa Banksort Inactive',
        'Zzz Banksort Active',
    ]);
});

it('bancos es catálogo global sin company_id y visible desde dos companies', function () {
    expect(Schema::hasColumn('banks', 'company_id'))->toBeFalse();

    $bank = Bank::factory()->create(['name' => 'Atlantis Bank', 'eu_code' => 'ES5555']);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $names = fn () => collect($this->getJson(route('admin.banks.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis Bank');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis Bank')
        ->and(Bank::query()->whereKey($bank->id)->exists())->toBeTrue();
});

it('BanksSeeder no falla y, si hay JSON v1, preserva ids y status', function () {
    $this->seed(BanksSeeder::class);

    $path = database_path('data/banks/banks.enriched.json');

    if (! is_file($path)) {
        expect(Bank::withTrashed()->count())->toBe(0);

        return;
    }

    /** @var list<array<string, mixed>> $rows */
    $rows = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    expect(Bank::withTrashed()->count())->toBe(count($rows))
        ->and(Bank::query()->whereKey(1)->first()?->status)->toBe(BankStatus::Active)
        ->and(Bank::query()->where('status', BankStatus::Active)->count())->toBe(17);

    $this->seed(BanksSeeder::class);

    expect(Bank::withTrashed()->count())->toBe(count($rows));
});
