<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    enableModuleForCompany($this->company, 'admin');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);

    $this->module = Module::factory()->create([
        'name' => 'Almacen',
        'slug' => 'almacen-fijo',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
        'icon' => 'warehouse',
        'color' => '#112233',
        'label' => ['es' => 'Almacén', 'en' => 'Warehouse', 'ca' => 'Magatzem'],
        'explanation' => ['es' => 'Stock', 'en' => 'Stock', 'ca' => 'Estoc'],
    ]);
});

/** @return array<string, mixed> */
function moduleUpdatePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'name' => 'Almacen Norte',
        'label' => ['es' => 'Almacén norte', 'en' => 'North warehouse', 'ca' => ''],
        'explanation' => ['es' => 'Stock norte', 'en' => '', 'ca' => ''],
        'color' => '#445566',
        'icon' => 'truck',
        'status' => ModuleStatus::Inactive->value,
    ], $overrides);
}

it('actualiza el módulo, conserva slug y level, y redirige al Edit', function () {
    $this->put(route('admin.modules.update', $this->module), moduleUpdatePayload([
        'slug' => 'hacked',
        'level' => ModuleLevel::Basic->value,
    ]))->assertRedirect();

    $this->module->refresh();

    expect($this->module->name)->toBe('Almacen Norte')
        ->and($this->module->slug)->toBe('almacen-fijo')
        ->and($this->module->level)->toBe(ModuleLevel::Optional)
        ->and($this->module->status)->toBe(ModuleStatus::Inactive)
        ->and($this->module->icon)->toBe('truck')
        ->and($this->module->color)->toBe('#445566')
        ->and($this->module->getTranslation('label', 'es'))->toBe('Almacén norte')
        ->and($this->module->getTranslation('explanation', 'es'))->toBe('Stock norte');
});

it('conserva slug y level cuando el payload omite ambos', function () {
    $this->put(route('admin.modules.update', $this->module), moduleUpdatePayload())
        ->assertRedirect();

    $this->module->refresh();

    expect($this->module->slug)->toBe('almacen-fijo')
        ->and($this->module->level)->toBe(ModuleLevel::Optional);
});

it('permite el mismo name del propio módulo sin distinguir mayúsculas', function () {
    $this->put(route('admin.modules.update', $this->module), moduleUpdatePayload([
        'name' => 'almacen',
    ]))->assertRedirect();

    expect($this->module->refresh()->name)->toBe('almacen')
        ->and($this->module->slug)->toBe('almacen-fijo');
});

it('rechaza un name que ya usa otro módulo sin distinguir mayúsculas', function () {
    Module::factory()->create([
        'name' => 'Ventas',
        'slug' => 'ventas-legacy',
    ]);

    $this->put(route('admin.modules.update', $this->module), moduleUpdatePayload([
        'name' => 'ventas',
    ]))->assertSessionHasErrors('name');

    expect($this->module->refresh()->name)->toBe('Almacen')
        ->and($this->module->slug)->toBe('almacen-fijo');
});

it('niega el update sin permiso modules.update', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->put(route('admin.modules.update', $this->module), moduleUpdatePayload())
        ->assertForbidden();

    expect($this->module->refresh()->name)->toBe('Almacen');
});

it('Edit declara tabs, kit y Volver fijo al Index', function () {
    $source = file_get_contents(resource_path('js/Pages/Admin/Modules/Edit.tsx'));

    expect($source)->toContain('backHref="/admin/modules"')
        ->and($source)->toContain('showBack')
        ->and($source)->toContain('IconPicker')
        ->and($source)->toContain('ColorPicker')
        ->and($source)->toContain('LangTabs')
        ->and($source)->toContain('<Switch')
        ->and($source)->toContain("t('modulo')")
        ->and($source)->toContain("t('funcionalidades')")
        ->and($source)->toContain('ServerTable')
        ->and($source)->toContain('functionalities/table')
        ->and($source)->toContain('nueva_funcionalidad')
        ->and($source)->toContain('RecordSummary')
        ->and($source)->toContain('auditRecordSummaryFields')
        ->and($source)->toContain('edicion_modulo')
        ->and($source)->toContain('defaultLocale')
        ->and($source)->toContain('module-level-readonly')
        ->and($source)->toContain("type: 'delete'")
        ->and($source)->not->toContain("locale === 'es'")
        ->and($source)->not->toContain('locale === "es"')
        ->and($source)->not->toContain("setData('level'")
        ->and($source)->not->toContain('setData("level"');
});

it('Edit entrega la ficha de auditoría del registro', function () {
    $this->get(route('admin.modules.edit', $this->module))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Modules/Edit')
            ->where('module.name', 'Almacen')
            ->where('module.created_by_name', $this->user->name)
            ->where('module.created_at', $this->module->created_at?->toIso8601String())
            ->where('module.updated_at', $this->module->updated_at?->toIso8601String()));
});
