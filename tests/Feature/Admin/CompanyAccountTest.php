<?php

use App\Domains\Admin\Actions\AssignDefaultCompanyAccount;
use App\Domains\Admin\Actions\ExpireCompanyAccounts;
use App\Domains\Admin\Actions\RenewCompanyAccount;
use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyAccount;
use App\Domains\Admin\Models\Role;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    enableModuleForCompany($this->company, 'cuenta');
    enableModuleForCompany($this->company, 'configuracion');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

it('CompanyAccount usa BelongsToCompany y no ve filas de otra company (R-TEN-05)', function () {
    expect(Schema::hasColumn('company_accounts', 'company_id'))->toBeTrue()
        ->and(Schema::hasColumn('company_accounts', 'guardian'))->toBeFalse();

    $other = Company::factory()->create();
    $mine = CompanyAccount::query()->withoutGlobalScope('company')->where('company_id', $this->company->id)->firstOrFail();
    $theirs = CompanyAccount::query()->withoutGlobalScope('company')->where('company_id', $other->id)->firstOrFail();

    withCurrentCompany($this->company);
    $visibleIds = CompanyAccount::query()->pluck('id');

    expect($visibleIds)->toContain($mine->id)
        ->and($visibleIds)->not->toContain($theirs->id);
});

it('renovar cancela la Active previa y deja como máximo una Active', function () {
    $catalog = Account::factory()->create(['status' => AccountStatus::Active, 'is_default' => false]);

    $renewed = app(RenewCompanyAccount::class)->handle($this->company, $catalog);

    $actives = CompanyAccount::query()
        ->withoutGlobalScope('company')
        ->where('company_id', $this->company->id)
        ->where('status', CompanyAccountStatus::Active)
        ->get();

    expect($actives)->toHaveCount(1)
        ->and($actives->first()?->id)->toBe($renewed->id)
        ->and(
            CompanyAccount::query()
                ->withoutGlobalScope('company')
                ->where('company_id', $this->company->id)
                ->where('status', CompanyAccountStatus::Cancelled)
                ->count(),
        )->toBeGreaterThanOrEqual(1);
});

it('AssignDefault falla si no hay Account default Active', function () {
    Account::query()->update(['is_default' => false]);

    $company = Company::query()->create([
        'name' => 'Orphan Co',
        'slug' => 'orphan-co-'.uniqid(),
        'tradename' => 'Orphan',
        'nif' => null,
        'status' => 'active',
        'logo' => null,
    ]);

    expect(fn () => app(AssignDefaultCompanyAccount::class)->handle($company))
        ->toThrow(ValidationException::class);
});

it('expire marca Active con end_date pasada como Expired', function () {
    $row = CompanyAccount::query()
        ->withoutGlobalScope('company')
        ->where('company_id', $this->company->id)
        ->where('status', CompanyAccountStatus::Active)
        ->firstOrFail();

    $row->forceFill([
        'end_date' => now()->subDay()->toDateString(),
    ])->save();

    expect(app(ExpireCompanyAccounts::class)->handle())->toBe(1)
        ->and($row->refresh()->status)->toBe(CompanyAccountStatus::Expired);

    Artisan::call('company-accounts:expire');
    expect(Artisan::output())->toContain('expired');
});

it('backfill es idempotente sobre companies Active sin fila', function () {
    $company = Company::query()->create([
        'name' => 'Backfill Co',
        'slug' => 'backfill-co-'.uniqid(),
        'tradename' => 'Backfill',
        'nif' => null,
        'status' => 'active',
        'logo' => null,
    ]);

    expect(
        CompanyAccount::query()->withoutGlobalScope('company')->where('company_id', $company->id)->exists(),
    )->toBeFalse();

    Artisan::call('company-accounts:backfill');
    Artisan::call('company-accounts:backfill');

    expect(
        CompanyAccount::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->where('status', CompanyAccountStatus::Active)
            ->count(),
    )->toBe(1);
});

it('dashboard responde 403 con CTA a renovación admin si la cuenta está caducada', function () {
    $row = CompanyAccount::query()
        ->withoutGlobalScope('company')
        ->where('company_id', $this->company->id)
        ->where('status', CompanyAccountStatus::Active)
        ->firstOrFail();

    $row->forceFill([
        'status' => CompanyAccountStatus::Expired,
        'end_date' => now()->subDay()->toDateString(),
    ])->save();

    $this->get(route('dashboard'))
        ->assertForbidden()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Errors/CompanyAccountRequired')
            ->where('renewUrl', route('admin.account.renew', absolute: false)));
});

it('histórico y renovación responden 200 con cuenta caducada (whitelist)', function () {
    $row = CompanyAccount::query()
        ->withoutGlobalScope('company')
        ->where('company_id', $this->company->id)
        ->where('status', CompanyAccountStatus::Active)
        ->firstOrFail();

    $row->forceFill([
        'status' => CompanyAccountStatus::Expired,
        'end_date' => now()->subDay()->toDateString(),
    ])->save();

    $this->get(route('admin.account.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Account/Show'));

    $this->get(route('admin.account.renew'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Account/Renew')
            ->has('catalogAccounts'));
});

it('histórico table solo lista company_accounts de CurrentCompany', function () {
    $other = Company::factory()->create();
    $otherRow = CompanyAccount::query()->withoutGlobalScope('company')->where('company_id', $other->id)->firstOrFail();

    $ids = collect($this->getJson(route('admin.account.company-accounts.table'))->assertOk()->json('data'))
        ->pluck('id');

    expect($ids)->not->toContain($otherRow->id);
});

it('POST renew con profile.update deja ≤1 Active también con Active vigente (upgrade)', function () {
    $catalog = Account::factory()->create(['status' => AccountStatus::Active, 'name' => 'Renew Plan']);

    expect(
        CompanyAccount::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $this->company->id)
            ->where('status', CompanyAccountStatus::Active)
            ->count(),
    )->toBe(1);

    $this->post(route('admin.account.renew.store'), ['account_id' => $catalog->id])
        ->assertRedirect(route('admin.account.show'));

    expect(
        CompanyAccount::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $this->company->id)
            ->where('status', CompanyAccountStatus::Active)
            ->count(),
    )->toBe(1)
        ->and(
            CompanyAccount::query()
                ->withoutGlobalScope('company')
                ->where('company_id', $this->company->id)
                ->where('status', CompanyAccountStatus::Active)
                ->value('account_id'),
        )->toBe($catalog->id);
});

it('admin.accounts.renewal redirige a admin.account.renew', function () {
    $this->get(route('admin.accounts.renewal'))
        ->assertRedirect(route('admin.account.renew'));
});

it('Admin/Account/Show usa ServerTable R-FE-04 y CTA a renew con icon', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Account/Show.tsx'));

    expect($source)->toContain('persistKey="admin.account.company-accounts"')
        ->and($source)->toContain("valueKind: 'date'")
        ->and($source)->toContain("valueKind: 'number'")
        ->and($source)->toContain('fmt.date')
        ->and($source)->toContain('fmt.money')
        ->and($source)->toContain('renovar_mejorar_cuenta')
        ->and($source)->toContain('icon: RefreshCw');
});

it('Renew usa AccountCatalogGrid y no quedan Pages/Account', function () {
    $renew = (string) file_get_contents(resource_path('js/Pages/Admin/Account/Renew.tsx'));
    $grid = (string) file_get_contents(resource_path('js/Components/Account/AccountCatalogGrid.tsx'));

    expect($renew)->toContain('AccountCatalogGrid')
        ->and($grid)->toContain('export function AccountCatalogGrid')
        ->and(file_exists(resource_path('js/Pages/Account/Show.tsx')))->toBeFalse()
        ->and(file_exists(resource_path('js/Pages/Account/ComingSoon.tsx')))->toBeFalse()
        ->and(file_exists(resource_path('js/Pages/Admin/Accounts/RenewalComingSoon.tsx')))->toBeFalse();
});

it('sin profile.view no abre Mi cuenta ni renovación', function () {
    $actor = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('cuenta.access');
    $actor->givePermissionTo(['cuenta.access']);
    actingWithCompany($actor, $this->company);

    $this->get(route('admin.account.show'))->assertForbidden();
    $this->get(route('admin.account.renew'))->assertForbidden();
});
