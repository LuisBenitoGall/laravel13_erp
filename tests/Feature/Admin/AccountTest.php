<?php

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Models\User;
use Database\Seeders\AccountsSeeder;
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

it('Super Admin abre Index de cuentas', function () {
    $this->get(route('admin.accounts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Accounts/Index'));
});

it('Index cuentas sin columna Estado y con Acciones toggle, Edit y Delete', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Accounts/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->toContain('persistKey="admin.accounts"')
        ->and($source)->toContain("field: 'rate'")
        ->and($source)->toContain("field: 'duration'")
        ->and($source)->toContain("valueKind: 'number'");
});

it('Index GET no crea filas (sin import lateral)', function () {
    $before = Account::withTrashed()->count();

    $this->get(route('admin.accounts.index'))->assertOk();
    $this->getJson(route('admin.accounts.table'))->assertOk();

    expect(Account::withTrashed()->count())->toBe($before);
});

it('sin accounts.* las rutas de cuentas responden 403', function () {
    $account = Account::factory()->create();
    $before = Account::query()->count();

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.accounts.index'))->assertForbidden();
    $this->getJson(route('admin.accounts.table'))->assertForbidden();
    $this->get(route('admin.accounts.renewal'))->assertForbidden();
    $this->post(route('admin.accounts.store'), ['name' => 'Nueva', 'rate' => 0])->assertForbidden();
    $this->put(route('admin.accounts.update', $account), ['name' => 'X', 'rate' => 0])->assertForbidden();
    $this->patch(route('admin.accounts.status', $account))->assertForbidden();
    $this->delete(route('admin.accounts.destroy', $account))->assertForbidden();

    expect(Account::query()->count())->toBe($before);
});

it('usuario no-SA no abre accounts aunque tenga permisos (AdminOnly = SA)', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $user->givePermissionTo(['configuracion.access', 'accounts.view']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.accounts.index'))->assertForbidden();
    $this->get(route('admin.accounts.renewal'))->assertForbidden();
});

it('seeder deja la Functionality accounts con permisos accounts.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "accounts.{$ability}")->exists())->toBeTrue();
    }
});

it('crea cuenta con slug, status Active, rate y duration', function () {
    $this->post(route('admin.accounts.store'), [
        'name' => 'Plan Oro',
        'rate' => '49.90',
        'duration' => 365,
        'description' => 'Plan anual',
    ])->assertRedirect();

    $account = Account::query()->where('name', 'Plan Oro')->firstOrFail();

    expect($account->slug)->toBe('plan-oro')
        ->and($account->status)->toBe(AccountStatus::Active)
        ->and((float) $account->rate)->toBe(49.9)
        ->and($account->duration)->toBe(365)
        ->and($account->is_default)->toBeFalse();

    $this->post(route('admin.accounts.store'), ['name' => 'Plan Oro', 'rate' => 0])
        ->assertSessionHasErrors('name');

    $this->post(route('admin.accounts.store'), ['rate' => 0])
        ->assertSessionHasErrors('name');
});

it('name de una cuenta borrada puede reutilizarse', function () {
    $old = Account::factory()->create(['name' => 'Reutilizable']);
    $old->delete();

    $this->post(route('admin.accounts.store'), ['name' => 'Reutilizable', 'rate' => 0])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Account::query()->where('name', 'Reutilizable')->count())->toBe(1);
});

it('update cambia name, slug, rate y duration', function () {
    $account = Account::factory()->create([
        'name' => 'Cuenta Vieja',
        'slug' => 'cuenta-vieja',
        'rate' => '10.00',
        'duration' => 30,
    ]);

    $this->put(route('admin.accounts.update', $account), [
        'name' => 'Cuenta Nueva',
        'rate' => '20.50',
        'duration' => 90,
    ])->assertRedirect();

    $account->refresh();

    expect($account->name)->toBe('Cuenta Nueva')
        ->and($account->slug)->toBe('cuenta-nueva')
        ->and((float) $account->rate)->toBe(20.5)
        ->and($account->duration)->toBe(90);

    $this->put(route('admin.accounts.update', $account), ['name' => '', 'rate' => 0])
        ->assertSessionHasErrors('name');
});

it('marcar is_default deja una sola default', function () {
    $first = Account::factory()->create(['name' => 'Primera']);
    $second = Account::factory()->create(['name' => 'Segunda']);

    $this->put(route('admin.accounts.update', $first), [
        'name' => $first->name,
        'rate' => $first->rate,
        'is_default' => true,
    ])->assertRedirect();

    expect($first->refresh()->is_default)->toBeTrue()
        ->and($second->refresh()->is_default)->toBeFalse();

    $this->put(route('admin.accounts.update', $second), [
        'name' => $second->name,
        'rate' => $second->rate,
        'is_default' => true,
    ])->assertRedirect();

    expect($second->refresh()->is_default)->toBeTrue()
        ->and($first->refresh()->is_default)->toBeFalse()
        ->and(Account::query()->where('is_default', true)->count())->toBe(1);
});

it('Edit no desmarca is_default de la cuenta ya default (UI disabled)', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Accounts/Index.tsx'));

    expect($source)->toContain('disabled={editing?.is_default === true}')
        ->and($source)->toContain('editing?.is_default === true && !checked');

    $account = Account::factory()->default()->create(['name' => 'Default']);

    $this->put(route('admin.accounts.update', $account), [
        'name' => $account->name,
        'rate' => $account->rate,
        'is_default' => false,
    ])->assertRedirect();

    expect($account->refresh()->is_default)->toBeTrue();
});

it('no permite borrar la cuenta default', function () {
    $account = Account::factory()->default()->create();

    $this->delete(route('admin.accounts.destroy', $account))
        ->assertSessionHasErrors('account');

    expect(Account::query()->whereKey($account->id)->exists())->toBeTrue();
});

it('toggle status alterna Active/Inactive también en la default', function () {
    $account = Account::factory()->default()->create();

    $this->patchJson(route('admin.accounts.status', $account))
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    expect($account->refresh()->status)->toBe(AccountStatus::Inactive)
        ->and($account->is_default)->toBeTrue();

    $this->patchJson(route('admin.accounts.status', $account), ['status' => 'active'])
        ->assertOk()
        ->assertJson(['status' => 'active']);
});

it('borra cuenta no-default con soft delete', function () {
    $account = Account::factory()->create();

    $this->delete(route('admin.accounts.destroy', $account))
        ->assertRedirect(route('admin.accounts.index'));

    expect(Account::query()->whereKey($account->id)->exists())->toBeFalse()
        ->and(Account::withTrashed()->whereKey($account->id)->exists())->toBeTrue();
});

it('table sin sorts aplica Active-first y luego name', function () {
    Account::factory()->create(['name' => 'Zzz Accsort Active', 'status' => AccountStatus::Active]);
    Account::factory()->inactive()->create(['name' => 'Aaa Accsort Inactive']);
    Account::factory()->create(['name' => 'Mmm Accsort Active', 'status' => AccountStatus::Active]);

    $rows = collect($this->getJson(route('admin.accounts.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Accsort']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Mmm Accsort Active',
        'Zzz Accsort Active',
        'Aaa Accsort Inactive',
    ]);
});

it('cuentas es catálogo global sin company_id y visible desde dos companies', function () {
    expect(Schema::hasColumn('accounts', 'company_id'))->toBeFalse();

    $account = Account::factory()->create(['name' => 'Atlantis Account']);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $names = fn () => collect($this->getJson(route('admin.accounts.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis Account');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis Account')
        ->and(Account::query()->whereKey($account->id)->exists())->toBeTrue();
});

it('AccountsSeeder deja ≥1 Active default e idempotente', function () {
    $this->seed(AccountsSeeder::class);

    expect(Account::query()->where('is_default', true)->where('status', AccountStatus::Active)->count())->toBe(1)
        ->and(Account::query()->whereKey(1)->value('name'))->toBe('Básica');

    $this->seed(AccountsSeeder::class);

    expect(Account::query()->where('is_default', true)->count())->toBe(1)
        ->and(Account::withTrashed()->whereKey(1)->exists())->toBeTrue();
});

it('admin.accounts.renewal redirige a renovación admin', function () {
    $this->get(route('admin.accounts.renewal'))
        ->assertRedirect(route('admin.account.renew'));
});
