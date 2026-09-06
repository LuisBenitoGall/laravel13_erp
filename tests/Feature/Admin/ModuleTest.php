<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('permite CRUD de Module al Super Admin', function () {
    $user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $user->assignRole('Super Admin');

    $this->actingAs($user)
        ->post(route('admin.modules.store'), [
            'name' => 'Sales',
            'slug' => 'sales',
            'label' => ['es' => 'Ventas', 'en' => 'Sales'],
            'color' => '#0369a1',
            'icon' => 'shopping-cart',
            'level' => ModuleLevel::Basic->value,
            'status' => ModuleStatus::Active->value,
        ])
        ->assertRedirect();

    expect(Module::query()->where('slug', 'sales')->exists())->toBeTrue()
        ->and(Permission::query()->where('name', 'sales.access')->exists())->toBeTrue();
});

it('niega Module index sin permiso', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.modules.index'))
        ->assertForbidden();
});

it('catálogo Module es visible globalmente', function () {
    Module::factory()->create(['slug' => 'shared']);

    expect(Module::query()->where('slug', 'shared')->count())->toBe(1);
});
