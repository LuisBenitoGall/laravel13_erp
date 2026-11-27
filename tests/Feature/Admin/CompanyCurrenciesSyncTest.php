<?php

use App\Domains\Admin\Actions\CreateCompany;
use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);

    Module::factory()->create([
        'slug' => 'empresas',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);

    if (! Account::query()->where('is_default', true)->exists()) {
        Account::factory()->default()->create(['status' => AccountStatus::Active]);
    }
});

/**
 * @param  list<string>  $extraPermissions
 */
function actingCompanyCurrencyEditor(Company $company, array $extraPermissions = []): User
{
    enableModuleForCompany($company, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $names = array_merge(
        ['companies.access', 'companies.view', 'companies.update'],
        $extraPermissions,
    );
    $user->givePermissionTo(
        collect($names)->map(fn (string $name) => Permission::findOrCreate($name))->all(),
    );
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('sincroniza monedas con default único', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanyCurrencyEditor($company);

    $eur = Currency::factory()->create(['code' => 'EUR', 'status' => CurrencyStatus::Active]);
    $usd = Currency::factory()->create(['code' => 'USD', 'status' => CurrencyStatus::Active]);

    $this->put(route('admin.companies.currencies.sync', $company), [
        'currency_ids' => [$eur->id, $usd->id],
        'default_currency_id' => $eur->id,
    ])->assertRedirect();

    $company->refresh()->load('currencies');
    expect($company->currencies)->toHaveCount(2)
        ->and($company->defaultCurrency()?->id)->toBe($eur->id)
        ->and($company->currencies->where('pivot.is_default', true))->toHaveCount(1);
});

it('sync vacío limpia monedas', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanyCurrencyEditor($company);
    $eur = Currency::factory()->create(['status' => CurrencyStatus::Active]);
    $company->currencies()->attach($eur->id, ['is_default' => true]);

    $this->put(route('admin.companies.currencies.sync', $company), [
        'currency_ids' => [],
        'default_currency_id' => null,
    ])->assertRedirect();

    expect($company->fresh()->currencies()->count())->toBe(0);
});

it('rechaza default fuera del set', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanyCurrencyEditor($company);
    $eur = Currency::factory()->create(['status' => CurrencyStatus::Active]);
    $usd = Currency::factory()->create(['status' => CurrencyStatus::Active]);

    $this->put(route('admin.companies.currencies.sync', $company), [
        'currency_ids' => [$eur->id],
        'default_currency_id' => $usd->id,
    ])->assertSessionHasErrors('default_currency_id');
});

it('Create Company no crea filas de monedas', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active]);
    $user = actingCompanyCurrencyEditor($session, ['companies.create']);

    $created = app(CreateCompany::class)->handle([
        'name' => 'Sin Moneda SL',
        'tradename' => null,
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active->value,
    ], $user);

    expect($created->currencies()->count())->toBe(0);
});

it('R-TEN-05: company A no muta monedas de B', function () {
    $companyA = Company::factory()->create(['status' => CompanyStatus::Active]);
    $companyB = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanyCurrencyEditor($companyA);

    $eur = Currency::factory()->create(['status' => CurrencyStatus::Active]);

    $this->put(route('admin.companies.currencies.sync', $companyB), [
        'currency_ids' => [$eur->id],
        'default_currency_id' => $eur->id,
    ])->assertForbidden();

    expect($companyB->fresh()->currencies()->count())->toBe(0);
});

it('Delegación sync no altera matriz', function () {
    $matrix = Company::factory()->create(['status' => CompanyStatus::Active, 'parent_id' => null]);
    $delegation = Company::factory()->create([
        'status' => CompanyStatus::Active,
        'parent_id' => $matrix->id,
    ]);

    enableModuleForCompany($matrix, 'empresas');
    $user = User::factory()->create(['status' => true]);
    $user->companies()->attach($matrix->id);
    actingWithCompany($user, $matrix);

    setPermissionsTeamId($matrix->id);
    $user->givePermissionTo(
        collect(['companies.access', 'delegations.view', 'delegations.update'])
            ->map(fn (string $name) => Permission::findOrCreate($name))
            ->all(),
    );

    $eur = Currency::factory()->create(['code' => 'EUR', 'status' => CurrencyStatus::Active]);
    $usd = Currency::factory()->create(['code' => 'USD', 'status' => CurrencyStatus::Active]);

    $matrix->currencies()->attach($eur->id, ['is_default' => true]);

    $this->put(route('admin.delegations.currencies.sync', $delegation), [
        'currency_ids' => [$usd->id],
        'default_currency_id' => $usd->id,
    ])->assertRedirect();

    expect($delegation->fresh()->defaultCurrency()?->id)->toBe($usd->id)
        ->and($matrix->fresh()->defaultCurrency()?->id)->toBe($eur->id);
});

it('Edit Company expone sección monedas en Info sin tab dedicado', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingCompanyCurrencyEditor($company);
    $eur = Currency::factory()->create(['code' => 'EUR', 'status' => CurrencyStatus::Active]);
    $company->currencies()->attach($eur->id, ['is_default' => true]);

    $this->get(route('admin.companies.edit', $company))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Companies/Edit')
            ->where('default_currency_id', $eur->id)
            ->has('currencies', 1)
            ->has('currency_options')
            ->where('can.update_company_currencies', true));

    $index = file_get_contents(resource_path('js/Pages/Admin/Companies/Edit.tsx'));
    expect($index)->toContain('CompanyCurrenciesPanel')
        ->and($index)->not->toContain("id: 'currencies'");
});

it('Edit Delegación expone monedas en Info sin tab dedicado', function () {
    $matrix = Company::factory()->create(['status' => CompanyStatus::Active, 'parent_id' => null]);
    $delegation = Company::factory()->create([
        'status' => CompanyStatus::Active,
        'parent_id' => $matrix->id,
    ]);

    enableModuleForCompany($matrix, 'empresas');
    $user = User::factory()->create(['status' => true]);
    $user->companies()->attach($matrix->id);
    actingWithCompany($user, $matrix);

    setPermissionsTeamId($matrix->id);
    $user->givePermissionTo(
        collect(['companies.access', 'delegations.view', 'delegations.update'])
            ->map(fn (string $name) => Permission::findOrCreate($name))
            ->all(),
    );

    $eur = Currency::factory()->create(['code' => 'EUR', 'status' => CurrencyStatus::Active]);
    $delegation->currencies()->attach($eur->id, ['is_default' => true]);

    $this->get(route('admin.delegations.edit', $delegation))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Delegations/Edit')
            ->where('default_currency_id', $eur->id)
            ->has('currencies', 1)
            ->has('currency_options')
            ->where('can.update_company_currencies', true));

    $source = file_get_contents(resource_path('js/Pages/Admin/Delegations/Edit.tsx'));
    expect($source)->toContain('CompanyCurrenciesPanel')
        ->and($source)->not->toContain("id: 'currencies'");
});

it('Create Delegación no crea filas de monedas', function () {
    $matrix = Company::factory()->create(['status' => CompanyStatus::Active, 'parent_id' => null]);
    enableModuleForCompany($matrix, 'empresas');
    $user = User::factory()->create(['status' => true]);
    $user->companies()->attach($matrix->id);
    actingWithCompany($user, $matrix);

    setPermissionsTeamId($matrix->id);
    $user->givePermissionTo(
        collect(['companies.access', 'delegations.view', 'delegations.create'])
            ->map(fn (string $name) => Permission::findOrCreate($name))
            ->all(),
    );

    $this->post(route('admin.delegations.store'), [
        'name' => 'Delegación Sin Moneda',
        'tradename' => null,
        'nif' => '87654321X',
        'status' => CompanyStatus::Active->value,
        'workplace' => [
            'name' => 'Sede temporal',
            'address' => 'Calle 1',
            'cp' => '28001',
        ],
    ])->assertRedirect();

    $delegation = Company::query()->where('parent_id', $matrix->id)->first();
    expect($delegation)->not->toBeNull()
        ->and($delegation->currencies()->count())->toBe(0);
});
