<?php

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Currency;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Services\CompanySetupReadiness;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create([
        'nif' => null,
        'logo' => null,
        'status' => CompanyStatus::Active,
    ]);
    enableModuleForCompany($this->company, 'empresas');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

it('menú Empresas incluye Configuración de la empresa', function () {
    $menu = app(MenuBuilder::class)->forUser($this->user);
    $empresas = collect($menu)->firstWhere('slug', 'empresas');
    $item = collect($empresas['functionalities'] ?? [])->firstWhere('slug', 'company-settings');

    expect($item)->not->toBeNull()
        ->and($item['label'])->toBe(__('configuracion_de_la_empresa'))
        ->and($item['href'])->toBe(route('admin.company-settings.index', absolute: false));
});

it('seeder deja permisos company-settings.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "company-settings.{$ability}")->exists())->toBeTrue();
    }
});

it('sin company-settings.view el hub responde 403', function () {
    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.company-settings.index'))->assertForbidden();
});

it('usuario con company-settings.view abre el hub', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('companies.access');
    $user->givePermissionTo(['companies.access', 'company-settings.view']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.company-settings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CompanySettings/Index')
            ->where('wizard', false)
            ->where('setup.complete', false));
});

it('readiness exige nif, logo, workplace Active y moneda por defecto', function () {
    $readiness = app(CompanySetupReadiness::class);

    expect($readiness->evaluate($this->company)['complete'])->toBeFalse()
        ->and($readiness->evaluate($this->company)['total_steps'])->toBe(3);

    $this->company->forceFill(['nif' => 'B12345678'])->save();
    expect($readiness->evaluate($this->company->fresh())['steps']['identity']['nif'])->toBeTrue()
        ->and($readiness->evaluate($this->company->fresh())['complete'])->toBeFalse();

    $this->company->forceFill(['logo' => 'companies/1/logo.png'])->save();
    expect($readiness->evaluate($this->company->fresh())['steps']['identity']['done'])->toBeTrue()
        ->and($readiness->evaluate($this->company->fresh())['complete'])->toBeFalse();

    Workplace::factory()->create([
        'company_id' => $this->company->id,
        'status' => WorkplaceStatus::Active,
    ]);

    expect($readiness->evaluate($this->company->fresh())['steps']['workplaces']['done'])->toBeTrue()
        ->and($readiness->evaluate($this->company->fresh())['complete'])->toBeFalse()
        ->and($readiness->evaluate($this->company->fresh())['steps']['currencies']['done'])->toBeFalse();

    $eur = Currency::factory()->create();
    $this->company->currencies()->attach($eur->id, ['is_default' => true]);

    expect($readiness->evaluate($this->company->fresh())['complete'])->toBeTrue();
});

it('shared props reflejan incomplete y show_modal de sesión', function () {
    session(['company_setup.show_modal' => true]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('company_setup.incomplete', true)
            ->where('company_setup.show_modal', true)
            ->where('company_setup.hub_url', route('admin.company-settings.index', absolute: false)));
});

it('dismiss modal limpia show_modal y redirige al dashboard', function () {
    session(['company_setup.show_modal' => true]);

    $this->post(route('admin.company-settings.dismiss-modal'))
        ->assertRedirect(route('dashboard'));

    expect(session()->has('company_setup.show_modal'))->toBeFalse();
});

it('store Mis empresas cambia sesión a la nueva y muestra modal si incompleta', function () {
    Storage::fake('public');

    $this->company->forceFill([
        'nif' => 'X1234567L',
        'logo' => 'companies/a/logo.png',
    ])->save();
    Workplace::factory()->create([
        'company_id' => $this->company->id,
        'status' => WorkplaceStatus::Active,
    ]);
    $eur = Currency::factory()->create();
    $this->company->currencies()->attach($eur->id, ['is_default' => true]);

    expect(app(CompanySetupReadiness::class)->evaluate($this->company->fresh())['complete'])->toBeTrue();
    expect(session('current_company_id'))->toBe($this->company->id);

    $response = $this->post(route('admin.companies.store'), [
        'name' => 'Empresa Modal',
        'tradename' => 'Modal',
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active->value,
    ]);

    $created = Company::query()->where('nif', '12345678Z')->firstOrFail();

    $response->assertRedirect(route('admin.companies.edit', $created));

    expect(session('current_company_id'))->toBe($created->id)
        ->and(session('company_setup.show_modal'))->toBeTrue()
        ->and(app(CompanySetupReadiness::class)->evaluate($created)['complete'])->toBeFalse();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('company_setup.incomplete', true)
            ->where('company_setup.show_modal', true)
            ->where('tenant.id', $created->id));
});

it('login con company incompleta solo banner (sin modal)', function () {
    session()->forget('company_setup.show_modal');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('company_setup.incomplete', true)
            ->where('company_setup.show_modal', false));
});

it('hub wizard=1 y listado de sedes; create workplace acepta return_to', function () {
    Workplace::factory()->inactive()->create([
        'company_id' => $this->company->id,
        'name' => 'Inactiva',
    ]);

    $this->get(route('admin.company-settings.index', ['wizard' => 1]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CompanySettings/Index')
            ->where('wizard', true)
            ->has('workplaces', 1));

    $this->get(route('admin.workplaces.create', [
        'return_to' => '/companies/company-settings?wizard=1',
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Workplaces/Create')
            ->where('return_to', '/companies/company-settings?wizard=1'));
});

it('banner desaparece cuando readiness completa', function () {
    Storage::fake('public');

    $this->company->forceFill([
        'nif' => 'X1234567L',
        'logo' => 'companies/x/logo.png',
    ])->save();

    Workplace::factory()->create([
        'company_id' => $this->company->id,
        'status' => WorkplaceStatus::Active,
    ]);
    $eur = Currency::factory()->create();
    $this->company->currencies()->attach($eur->id, ['is_default' => true]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('company_setup.incomplete', false)
            ->where('company_setup.show_modal', false));
});

it('hub sync monedas completa el paso currencies', function () {
    $this->company->forceFill([
        'nif' => 'B12345678',
        'logo' => 'companies/1/logo.png',
    ])->save();
    Workplace::factory()->create([
        'company_id' => $this->company->id,
        'status' => WorkplaceStatus::Active,
    ]);

    setPermissionsTeamId($this->company->id);
    $this->user->givePermissionTo(Permission::findOrCreate('company-settings.update'));

    $eur = Currency::factory()->create();

    $this->put(route('admin.company-settings.currencies.sync'), [
        'currency_ids' => [$eur->id],
        'default_currency_id' => $eur->id,
    ])->assertRedirect();

    expect(app(CompanySetupReadiness::class)->evaluate($this->company->fresh())['complete'])->toBeTrue()
        ->and(app(CompanySetupReadiness::class)->evaluate($this->company->fresh())['steps']['currencies']['done'])->toBeTrue();
});

it('Index hub no inventa pasos futuros y wizard exige logo antes de Sedes', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/CompanySettings/Index.tsx'));

    expect($source)->toContain('paso_identidad')
        ->and($source)->toContain('paso_sedes')
        ->and($source)->toContain('paso_monedas')
        ->and($source)->toContain('logoOk')
        ->and($source)->toContain('setup.steps.identity.logo')
        ->and($source)->not->toContain('accounting_account')
        ->and($source)->not->toContain('sepa')
        ->and($source)->not->toContain('payment-methods')
        ->and($source)->not->toContain('update_settings');
});
