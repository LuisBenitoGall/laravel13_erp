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

it('Modules Index declara breadcrumb con Dashboard y href al index', function () {
    $source = file_get_contents(resource_path('js/Pages/Admin/Modules/Index.tsx'));

    expect($source)->toContain("optionKey: 'modulos'")
        ->and($source)->toContain("suboptionKey: 'dashboard'")
        ->and($source)->toContain("optionHref: '/admin/modules'");
});

it('Modules Edit declara suboption editar y option enlaza al index', function () {
    $source = file_get_contents(resource_path('js/Pages/Admin/Modules/components/ModuleForm.tsx'));

    expect($source)->toContain("optionKey: 'modulos'")
        ->and($source)->toContain('modulo_editar')
        ->and($source)->toContain("optionHref: '/admin/modules'");
});

it('Index y Edit de modules responden OK con AppLayout', function () {
    $module = Module::factory()->create([
        'slug' => 'sales',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);

    $this->get(route('admin.modules.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Modules/Index'));

    $this->get(route('admin.modules.edit', $module))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Modules/Edit'));
});
