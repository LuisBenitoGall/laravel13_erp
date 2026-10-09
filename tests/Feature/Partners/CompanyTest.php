<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Actions\CreateCompany;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('es visible igual desde cualquier contexto de empresa (catálogo global)', function () {
    $company = Company::factory()->create();

    expect(Company::query()->whereKey($company->id)->exists())->toBeTrue();
});

it('permite CRUD de Company al Super Admin con empresa de sesión', function () {
    $workspace = Company::factory()->create(['slug' => 'workspace']);
    $adminModule = Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($workspace, 'admin');

    $user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $user->assignRole('Super Admin');
    actingWithCompany($user, $workspace);

    $this->post(route('partners.companies.store'), [
        'name' => 'Acme',
        'slug' => 'acme',
        'tradename' => 'Acme SL',
        'nif' => 'B12345678',
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ])->assertRedirect();

    expect(Company::query()->where('slug', 'acme')->exists())->toBeTrue();
});

it('niega Company index sin permiso', function () {
    $workspace = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($workspace, 'admin');

    $user = User::factory()->create();
    actingWithCompany($user, $workspace);

    $this->get(route('partners.companies.index'))->assertForbidden();
});

it('al crear company activa autoriza Modules Basic', function () {
    Module::factory()->create([
        'slug' => 'sales',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'slug' => 'premium',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);

    $company = app(CreateCompany::class)->handle([
        'name' => 'Basic Co',
        'slug' => 'basic-co',
        'tradename' => 'Basic',
        'nif' => null,
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ]);

    $authorizedSlugs = CompanyModuleAuthorization::query()->withoutGlobalScope('company')
        ->where('company_id', $company->id)
        ->with('module')
        ->get()
        ->pluck('module.slug')
        ->all();

    expect($authorizedSlugs)->toContain('sales')
        ->and($authorizedSlugs)->not->toContain('premium');
});
