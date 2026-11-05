<?php

use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\CanonicalPermission;
use App\Domains\Partners\Models\Company;
use App\Models\User;
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

    $this->functionality = Functionality::query()->where('slug', 'modules')->firstOrFail();
});

it('CanonicalPermission distingue canónicos, access y custom', function () {
    expect(CanonicalPermission::isCanonical('modules.viewAny'))->toBeTrue()
        ->and(CanonicalPermission::isCanonical('configuracion.access'))->toBeTrue()
        ->and(CanonicalPermission::isCanonical('modules.export'))->toBeFalse()
        ->and(CanonicalPermission::isReservedSuffix('view'))->toBeTrue()
        ->and(CanonicalPermission::isReservedSuffix('export'))->toBeFalse()
        ->and(CanonicalPermission::functionalitySlug('modules.export'))->toBe('modules');
});

it('Permission es catálogo global Spatie sin company_id (R-TEN-05 N/A)', function () {
    expect(Permission::query()->first())->not->toBeNull()
        ->and(Schema::hasColumn('permissions', 'company_id'))->toBeFalse();
});

it('seeder deja Functionality permissions y Spatie permissions.* + configuracion.access', function () {
    expect(Functionality::query()->where('slug', 'permissions')->where('status', FunctionalityStatus::Active)->exists())->toBeTrue();

    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "permissions.{$ability}")->exists())->toBeTrue();
    }

    expect(Permission::query()->where('name', 'configuracion.access')->exists())->toBeTrue();
});

it('Index requiere permissions.viewAny', function () {
    $guest = User::factory()->create();
    actingWithCompany($guest, $this->company);

    $this->get(route('admin.permissions.index'))->assertForbidden();
    $this->getJson(route('admin.permissions.table'))->assertForbidden();

    $permission = Permission::query()->where('name', 'modules.viewAny')->firstOrFail();

    $this->post(route('admin.permissions.store'), [
        'functionality_id' => $this->functionality->id,
        'suffix' => 'export',
    ])->assertForbidden();

    $this->delete(route('admin.permissions.destroy', $permission))->assertForbidden();
    expect(Permission::query()->whereKey($permission->id)->exists())->toBeTrue();
});

it('bloquea permissions si configuracion no está activo para la company (V-21)', function () {
    $module = Module::query()->where('slug', 'configuracion')->firstOrFail();
    app(DisableModuleForCompany::class)->handle($this->company, $module);

    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo('permissions.viewAny');
    actingWithCompany($user, $this->company);

    $this->get(route('admin.permissions.index'))->assertForbidden();
});

it('permite Index con configuracion activo, comodín y permissions.viewAny (AND)', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo(['configuracion.access', 'permissions.viewAny']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.permissions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Permissions/Index'));
});

it('403 si solo viewAny sin comodín configuracion.access (AND HTTP)', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo('permissions.viewAny');
    actingWithCompany($user, $this->company);

    $this->get(route('admin.permissions.index'))->assertForbidden();
});

it('Super Admin abre Index de permisos', function () {
    $this->get(route('admin.permissions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Permissions/Index')
            ->has('functionalities'));
});

it('table lista permisos con functionality_label y can_delete', function () {
    Permission::findOrCreate('modules.export');

    $response = $this->getJson(route('admin.permissions.table', [
        'filter' => [
            ['field' => 'name', 'type' => 'like', 'value' => 'modules.export'],
        ],
    ]))->assertOk();

    $rows = collect($response->json('data'));
    $custom = $rows->firstWhere('name', 'modules.export');

    expect($custom)->not->toBeNull()
        ->and($custom['can_delete'])->toBeTrue()
        ->and($custom['functionality_slug'])->toBe('modules');

    $canonicalResponse = $this->getJson(route('admin.permissions.table', [
        'filter' => [
            ['field' => 'name', 'type' => 'like', 'value' => 'modules.viewAny'],
        ],
    ]))->assertOk();

    $canonical = collect($canonicalResponse->json('data'))->firstWhere('name', 'modules.viewAny');

    expect($canonical)->not->toBeNull()
        ->and($canonical['can_delete'])->toBeFalse();
});

it('crea permiso custom válido', function () {
    $this->post(route('admin.permissions.store'), [
        'functionality_id' => $this->functionality->id,
        'suffix' => 'export-pdf',
    ])->assertRedirect();

    expect(Permission::query()->where('name', 'modules.export-pdf')->exists())->toBeTrue();
});

it('rechaza sufijo reservado', function () {
    $this->post(route('admin.permissions.store'), [
        'functionality_id' => $this->functionality->id,
        'suffix' => 'viewAny',
    ])->assertSessionHasErrors('suffix');

    $this->post(route('admin.permissions.store'), [
        'functionality_id' => $this->functionality->id,
        'suffix' => 'access',
    ])->assertSessionHasErrors('suffix');
});

it('rechaza name duplicado', function () {
    Permission::findOrCreate('modules.export');

    $this->post(route('admin.permissions.store'), [
        'functionality_id' => $this->functionality->id,
        'suffix' => 'export',
    ])->assertSessionHasErrors('suffix');
});

it('borra permiso custom y protege canónicos y access', function () {
    $custom = Permission::findOrCreate('modules.export');
    $canonical = Permission::query()->where('name', 'modules.viewAny')->firstOrFail();
    $access = Permission::query()->where('name', 'configuracion.access')->firstOrFail();

    $this->delete(route('admin.permissions.destroy', $custom))->assertRedirect();
    expect(Permission::query()->whereKey($custom->id)->exists())->toBeFalse();

    $this->delete(route('admin.permissions.destroy', $canonical))->assertForbidden();
    expect(Permission::query()->whereKey($canonical->id)->exists())->toBeTrue();

    $this->delete(route('admin.permissions.destroy', $access))->assertForbidden();
    expect(Permission::query()->whereKey($access->id)->exists())->toBeTrue();
});
