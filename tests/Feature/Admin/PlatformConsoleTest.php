<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\CompanyModuleAuthorization;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    Role::findOrCreate(Role::SUPER_ADMIN);

    $this->linked = Company::factory()->create([
        'slug' => 'linked-co',
        'status' => CompanyStatus::Active,
    ]);
    $this->foreign = Company::factory()->create([
        'slug' => 'foreign-co',
        'status' => CompanyStatus::Active,
    ]);

    $this->admin = Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($this->linked, 'admin');

    $this->optional = Module::factory()->create([
        'slug' => 'optional-mod',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);

    $this->sa = User::factory()->create();
    $this->sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($this->sa, $this->linked);
});

it('SA table solo lista companies vinculadas', function () {
    $response = $this->getJson(route('partners.companies.table'));
    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id')->all();
    expect($ids)->toContain($this->linked->id)
        ->and($ids)->not->toContain($this->foreign->id);
});

it('authorizations selector no incluye companies ajenas', function () {
    $this->get(route('admin.company-module-authorizations.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyModuleAuthorizations/Index')
            ->where('companies', function ($companies) {
                $ids = collect($companies)->pluck('id')->all();

                return in_array($this->linked->id, $ids, true)
                    && ! in_array($this->foreign->id, $ids, true);
            }));
});

it('authorize/revoke company no vinculada responde 403', function () {
    $this->post(route('admin.company-module-authorizations.authorize', [
        $this->foreign,
        $this->optional,
    ]))->assertForbidden();

    $this->post(route('admin.company-module-authorizations.revoke', [
        $this->foreign,
        $this->optional,
    ]))->assertForbidden();
});

it('authorize en company vinculada sigue OK', function () {
    $this->post(route('admin.company-module-authorizations.authorize', [
        $this->linked,
        $this->optional,
    ]))->assertRedirect();

    expect(
        CompanyModuleAuthorization::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $this->linked->id)
            ->where('module_id', $this->optional->id)
            ->exists(),
    )->toBeTrue();
});

it('edit de company no vinculada responde 403', function () {
    $this->get(route('partners.companies.edit', $this->foreign))->assertForbidden();
});

it('query company_id ajena en authorizations responde 403', function () {
    $this->get(route('admin.company-module-authorizations.index', [
        'company_id' => $this->foreign->id,
    ]))->assertForbidden();
});
