<?php

use App\Domains\Admin\Enums\UnitStatus;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Unit;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\UnitsSeeder;
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
    enableModuleForCompany($this->company, 'admin');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

it('Super Admin abre Index de unidades', function () {
    $this->get(route('admin.units.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Units/Index'));
});

it('Index unidades sin columna Estado y con Acciones toggle, Edit y Delete', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Units/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->toContain('persistKey="admin.units"');
});

it('Index GET no crea filas (sin import lateral)', function () {
    expect(Unit::withTrashed()->count())->toBe(0);

    $this->get(route('admin.units.index'))->assertOk();
    $this->getJson(route('admin.units.table'))->assertOk();

    expect(Unit::withTrashed()->count())->toBe(0);
});

it('sin units.* las rutas de unidades responden 403', function () {
    $unit = Unit::factory()->create();

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.units.index'))->assertForbidden();
    $this->getJson(route('admin.units.table'))->assertForbidden();
    $this->post(route('admin.units.store'), ['name' => 'Nueva'])->assertForbidden();
    $this->put(route('admin.units.update', $unit), ['name' => 'X'])->assertForbidden();
    $this->patch(route('admin.units.status', $unit))->assertForbidden();
    $this->delete(route('admin.units.destroy', $unit))->assertForbidden();

    expect(Unit::query()->count())->toBe(1);
});

it('usuario con units.viewAny abre el Index', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo('units.viewAny');
    actingWithCompany($user, $this->company);

    $this->get(route('admin.units.index'))->assertOk();
});

it('seeder deja la Functionality units con permisos units.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "units.{$ability}")->exists())->toBeTrue();
    }
});

it('crea unidad con slug, status Active, name unique y symbol max 4', function () {
    $this->post(route('admin.units.store'), [
        'name' => 'Caja Narnia',
        'symbol' => 'CJ',
    ])->assertRedirect();

    $unit = Unit::query()->where('name', 'Caja Narnia')->firstOrFail();

    expect($unit->slug)->toBe('caja-narnia')
        ->and($unit->status)->toBe(UnitStatus::Active)
        ->and($unit->symbol)->toBe('CJ');

    $this->post(route('admin.units.store'), ['name' => 'Caja Narnia'])
        ->assertSessionHasErrors('name');

    $this->post(route('admin.units.store'), ['name' => 'Larga', 'symbol' => 'ABCDE'])
        ->assertSessionHasErrors('symbol');

    $this->post(route('admin.units.store'), ['symbol' => 'X'])
        ->assertSessionHasErrors('name');
});

it('name de una unidad borrada puede reutilizarse', function () {
    $old = Unit::factory()->create(['name' => 'Reutilizable']);
    $old->delete();

    $this->post(route('admin.units.store'), ['name' => 'Reutilizable'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Unit::query()->where('name', 'Reutilizable')->count())->toBe(1);
});

it('update cambia name, slug y symbol', function () {
    $unit = Unit::factory()->create([
        'name' => 'Unidad Vieja',
        'slug' => 'unidad-vieja',
        'symbol' => 'UV',
    ]);

    $this->put(route('admin.units.update', $unit), [
        'name' => 'Unidad Nueva',
        'symbol' => 'UN',
    ])->assertRedirect();

    $unit->refresh();

    expect($unit->name)->toBe('Unidad Nueva')
        ->and($unit->slug)->toBe('unidad-nueva')
        ->and($unit->symbol)->toBe('UN');

    $this->put(route('admin.units.update', $unit), ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('toggle status alterna Active/Inactive', function () {
    $unit = Unit::factory()->create();

    $this->patchJson(route('admin.units.status', $unit))
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    $this->patchJson(route('admin.units.status', $unit), ['status' => 'active'])
        ->assertOk()
        ->assertJson(['status' => 'active']);

    expect($unit->refresh()->status)->toBe(UnitStatus::Active);
});

it('borra unidad con soft delete', function () {
    $unit = Unit::factory()->create();

    $this->delete(route('admin.units.destroy', $unit))
        ->assertRedirect(route('admin.units.index'));

    expect(Unit::query()->whereKey($unit->id)->exists())->toBeFalse()
        ->and(Unit::withTrashed()->whereKey($unit->id)->exists())->toBeTrue();
});

it('table sin sorts aplica Active-first y luego name', function () {
    Unit::factory()->create(['name' => 'Zzz Unitsort Active', 'status' => UnitStatus::Active]);
    Unit::factory()->inactive()->create(['name' => 'Aaa Unitsort Inactive']);
    Unit::factory()->create(['name' => 'Mmm Unitsort Active', 'status' => UnitStatus::Active]);

    $rows = collect($this->getJson(route('admin.units.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Unitsort']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Mmm Unitsort Active',
        'Zzz Unitsort Active',
        'Aaa Unitsort Inactive',
    ]);
});

it('table con sort por name no prefija Active-first', function () {
    Unit::factory()->create(['name' => 'Zzz Unitsort Active', 'status' => UnitStatus::Active]);
    Unit::factory()->inactive()->create(['name' => 'Aaa Unitsort Inactive']);

    $rows = collect($this->getJson(route('admin.units.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Unitsort']],
        'sort' => [['field' => 'name', 'dir' => 'asc']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Aaa Unitsort Inactive',
        'Zzz Unitsort Active',
    ]);
});

it('unidades es catálogo global sin company_id y visible desde dos companies', function () {
    expect(Schema::hasColumn('units', 'company_id'))->toBeFalse();

    $unit = Unit::factory()->create(['name' => 'Atlantis Unit']);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'admin');

    $names = fn () => collect($this->getJson(route('admin.units.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis Unit');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis Unit')
        ->and(Unit::query()->whereKey($unit->id)->exists())->toBeTrue();
});

it('UnitsSeeder no falla y, si hay JSON v1, preserva ids y status', function () {
    $this->seed(UnitsSeeder::class);

    $path = database_path('data/units/units.json');

    if (! is_file($path)) {
        expect(Unit::withTrashed()->count())->toBe(0);

        return;
    }

    /** @var list<array<string, mixed>> $rows */
    $rows = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    expect(Unit::withTrashed()->count())->toBe(count($rows))
        ->and(Unit::query()->whereKey(1)->value('name'))->toBe('Unidad')
        ->and(Unit::query()->whereKey(1)->first()?->status)->toBe(UnitStatus::Active)
        ->and(Unit::query()->where('status', UnitStatus::Active)->count())->toBe(30);

    $this->seed(UnitsSeeder::class);

    expect(Unit::withTrashed()->count())->toBe(count($rows));
});
