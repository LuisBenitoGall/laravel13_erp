<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->company = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'slug' => 'workspace',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($this->company, 'admin');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

it('soft-deletea un módulo no shell y redirige al Index', function () {
    $module = Module::factory()->create([
        'name' => 'Albaranes',
        'slug' => 'albaranes',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);
    Permission::findOrCreate('albaranes.access');

    $this->delete(route('admin.modules.destroy', $module))
        ->assertRedirect(route('admin.modules.index'));

    expect(Module::query()->whereKey($module->id)->exists())->toBeFalse()
        ->and(Module::withTrashed()->whereKey($module->id)->exists())->toBeTrue()
        ->and(Permission::query()->where('name', 'albaranes.access')->exists())->toBeTrue();
});

it('rechaza soft delete de módulos shell admin y workspace', function () {
    $admin = Module::query()->where('slug', 'admin')->firstOrFail();
    $workspace = Module::query()->where('slug', 'workspace')->firstOrFail();

    $this->delete(route('admin.modules.destroy', $admin))->assertForbidden();
    $this->delete(route('admin.modules.destroy', $workspace))->assertForbidden();

    expect(Module::query()->where('slug', 'admin')->exists())->toBeTrue()
        ->and(Module::query()->where('slug', 'workspace')->exists())->toBeTrue();
});

it('Edit expone level en la ficha del módulo', function () {
    $module = Module::factory()->create([
        'slug' => 'ventas',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);

    $this->get(route('admin.modules.edit', $module))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Modules/Edit')
            ->where('module.level', ModuleLevel::Optional->value)
            ->where('module.slug', 'ventas'));
});
