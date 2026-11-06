<?php

use App\Domains\Admin\Models\AccountingAccountType;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Database\Seeders\AccountingAccountTypesSeeder;
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
});

it('Super Admin abre Index de grupos contables', function () {
    $this->get(route('admin.accounting-account-types.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/AccountingAccountTypes/Index'));
});

it('Index sin toggle status y con Edit, Delete y enlace a show', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/AccountingAccountTypes/Index.tsx'));

    expect($source)->not->toContain("kind: 'toggle'")
        ->and($source)->not->toContain("field: 'status'")
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->toContain('ver_plan_pgc')
        ->and($source)->toContain('persistKey="admin.accounting-account-types"');
});

it('sin accounting-account-types.* responde 403', function () {
    $type = AccountingAccountType::factory()->create();

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.accounting-account-types.index'))->assertForbidden();
    $this->get(route('admin.accounting-account-types.show'))->assertForbidden();
    $this->getJson(route('admin.accounting-account-types.table'))->assertForbidden();
    $this->post(route('admin.accounting-account-types.store'), ['code' => '9', 'name' => 'X'])->assertForbidden();
    $this->put(route('admin.accounting-account-types.update', $type), ['name' => 'Y'])->assertForbidden();
    $this->delete(route('admin.accounting-account-types.destroy', $type))->assertForbidden();
});

it('usuario con view y viewAny abre Index y show', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $user->givePermissionTo(['configuracion.access', 'accounting-account-types.view', 'accounting-account-types.viewAny']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.accounting-account-types.index'))->assertOk();
    $this->get(route('admin.accounting-account-types.show'))->assertOk();
});

it('seeder deja Functionality accounting-account-types con permisos', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "accounting-account-types.{$ability}")->exists())->toBeTrue();
    }
});

it('crea grupo raíz con code unique y update no muta code', function () {
    $this->post(route('admin.accounting-account-types.store'), [
        'code' => '8',
        'name' => 'Gastos',
    ])->assertRedirect();

    $type = AccountingAccountType::query()->where('code', '8')->firstOrFail();

    expect($type->autoreference)->toBe(0)
        ->and($type->name)->toBe('Gastos');

    $this->post(route('admin.accounting-account-types.store'), [
        'code' => '8',
        'name' => 'Otro',
    ])->assertSessionHasErrors('code');

    $this->put(route('admin.accounting-account-types.update', $type), [
        'name' => 'Gastos nuevos',
        'code' => '99',
    ])->assertRedirect();

    $type->refresh();

    expect($type->name)->toBe('Gastos nuevos')
        ->and($type->code)->toBe('8');
});

it('no elimina grupo con hijos no trashed', function () {
    $parent = AccountingAccountType::factory()->create(['code' => '1', 'name' => 'Padre']);
    AccountingAccountType::factory()->create([
        'autoreference' => $parent->id,
        'code' => '10',
        'name' => 'Hijo',
    ]);

    $this->delete(route('admin.accounting-account-types.destroy', $parent))
        ->assertRedirect()
        ->assertSessionHasErrors('accounting_account_type');

    expect(AccountingAccountType::query()->whereKey($parent->id)->exists())->toBeTrue();
});

it('borra grupo hoja con soft delete', function () {
    $type = AccountingAccountType::factory()->create(['code' => '7', 'name' => 'Hoja']);

    $this->delete(route('admin.accounting-account-types.destroy', $type))
        ->assertRedirect(route('admin.accounting-account-types.index'));

    expect(AccountingAccountType::query()->whereKey($type->id)->exists())->toBeFalse()
        ->and(AccountingAccountType::withTrashed()->whereKey($type->id)->exists())->toBeTrue();
});

it('table ordena por code y expone level', function () {
    AccountingAccountType::factory()->create(['code' => '2', 'name' => 'Dos']);
    AccountingAccountType::factory()->create(['code' => '10', 'name' => 'Diez']);
    AccountingAccountType::factory()->create(['code' => '1', 'name' => 'Uno']);

    $rows = collect($this->getJson(route('admin.accounting-account-types.table', [
        'size' => 50,
    ]))->assertOk()->json('data'));

    expect($rows->pluck('code')->all())->toBe(['1', '10', '2'])
        ->and($rows->firstWhere('code', '10')['level'])->toBe(2);
});

it('show construye árbol desde BD', function () {
    $parent = AccountingAccountType::factory()->create(['code' => '1', 'name' => 'Raiz', 'autoreference' => 0]);
    AccountingAccountType::factory()->create([
        'code' => '10',
        'name' => 'Hijo',
        'autoreference' => $parent->id,
    ]);

    $this->get(route('admin.accounting-account-types.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/AccountingAccountTypes/Show')
            ->has('tree', 1)
            ->where('tree.0.code', '1')
            ->has('tree.0.children', 1)
            ->where('tree.0.children.0.code', '10'));
});

it('catálogo global sin company_id visible desde dos companies', function () {
    expect(Schema::hasColumn('accounting_account_types', 'company_id'))->toBeFalse();

    AccountingAccountType::factory()->create(['code' => '5', 'name' => 'Atlantis']);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $names = fn () => collect($this->getJson(route('admin.accounting-account-types.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis');
});

it('AccountingAccountTypesSeeder siembra PGC con code 1', function () {
    $this->seed(AccountingAccountTypesSeeder::class);

    expect(AccountingAccountType::query()->count())->toBeGreaterThan(50)
        ->and(AccountingAccountType::query()->where('code', '1')->exists())->toBeTrue()
        ->and(AccountingAccountType::query()->where('autoreference', 0)->count())->toBeGreaterThan(0);

    expect(
        AccountingAccountType::query()
            ->where(function ($q): void {
                $q->where('name', 'like', 'Fiscal.%')
                    ->orWhere('name', 'like', 'fiscal.%');
            })
            ->count()
    )->toBe(0);

    $this->seed(AccountingAccountTypesSeeder::class);

    expect(AccountingAccountType::query()->where('code', '1')->count())->toBe(1);
});

it('claves fiscal del seeder están cubiertas en lang/es/fiscal.php', function () {
    $seeder = (string) file_get_contents(database_path('seeders/AccountingAccountTypesSeeder.php'));
    preg_match_all("/__\\('fiscal\\.([^']+)'\\)/", $seeder, $matches);
    $used = array_values(array_unique($matches[1]));
    $esKeys = array_keys(include lang_path('es/fiscal.php'));

    expect(array_diff($used, $esKeys))->toBe([]);
});
