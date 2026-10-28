<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
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

    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);

    $this->company = Company::factory()->create([
        'status' => CompanyStatus::Active,
    ]);

    $this->user = User::factory()->create();
    $this->user->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($this->user, $this->company);
});

it('Modules Index declara Crear en ViewActionBar y sin Volver', function () {
    $source = file_get_contents(resource_path('js/Pages/Admin/Modules/Index.tsx'));

    expect($source)->toContain("labelKey: 'crear'")
        ->and($source)->toContain("href: '/admin/modules/create'")
        ->and($source)->toContain('actions={[')
        ->and($source)->toContain('showBack={false}');
});

it('Create Modules declara actions vacío (Volver solo si hay origen en cliente)', function () {
    $source = file_get_contents(resource_path('js/Pages/Admin/Modules/components/ModuleForm.tsx'));

    expect($source)->toContain('actions={[]}')
        ->and($source)->not->toContain("labelKey: 'crear'");
});

it('navOrigin helper y ViewActionBar existen', function () {
    expect(file_exists(resource_path('js/lib/navOrigin.ts')))->toBeTrue()
        ->and(file_exists(resource_path('js/Components/ViewActionBar.tsx')))->toBeTrue()
        ->and(file_get_contents(resource_path('js/lib/navOrigin.ts')))->toContain('erp.nav.origin')
        ->and(file_get_contents(resource_path('js/Components/ViewActionBar.tsx')))->toContain('resolveBackHref');
});

it('Index y Create de modules responden OK', function () {
    $this->get(route('admin.modules.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Modules/Index'));

    $this->get(route('admin.modules.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Modules/Create'));
});
