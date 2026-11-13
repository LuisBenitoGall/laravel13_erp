<?php

use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
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
        'slug' => 'configuracion',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($this->company, 'configuracion');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);

    $this->module = Module::factory()->create([
        'name' => 'Albaranes',
        'slug' => 'albaranes',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);

    $this->otherModule = Module::factory()->create([
        'name' => 'Compras',
        'slug' => 'compras',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Active,
    ]);
});

/** @return array<string, mixed> */
function functionalityStorePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'name' => 'Listado albaranes',
        'label' => ['es' => 'Listado', 'en' => 'Listing', 'ca' => ''],
    ], $overrides);
}

it('table nested solo devuelve funcionalidades del módulo', function () {
    $own = Functionality::factory()->create([
        'module_id' => $this->module->id,
        'name' => 'Own',
        'slug' => 'own-fn',
    ]);
    Functionality::factory()->create([
        'module_id' => $this->otherModule->id,
        'name' => 'Other',
        'slug' => 'other-fn',
    ]);

    $response = $this->getJson(route('admin.modules.functionalities.table', $this->module))
        ->assertOk();

    $ids = collect($response->json('data'))->pluck('id');

    expect($ids)->toContain($own->id)
        ->and($ids)->not->toContain(
            Functionality::query()->where('slug', 'other-fn')->value('id'),
        );
});

it('store nested crea con slug auto, status Active y provisiona permisos', function () {
    $this->post(route('admin.modules.functionalities.store', $this->module), functionalityStorePayload())
        ->assertRedirect(route('admin.modules.edit', [
            'module' => $this->module,
            'tab' => 'functionalities',
        ]));

    $functionality = Functionality::query()->where('slug', 'listado-albaranes')->first();

    expect($functionality)->not->toBeNull()
        ->and($functionality->module_id)->toBe($this->module->id)
        ->and($functionality->name)->toBe('Listado albaranes')
        ->and($functionality->status)->toBe(FunctionalityStatus::Active)
        ->and($functionality->getTranslation('label', 'es'))->toBe('Listado');

    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "listado-albaranes.{$ability}")->exists())->toBeTrue();
    }

    expect(Permission::query()->where('name', 'albaranes.access')->exists())->toBeTrue();
});

it('store nested rechaza name duplicado sin distinguir mayúsculas', function () {
    Functionality::factory()->create([
        'module_id' => $this->module->id,
        'name' => 'Listado Albaranes',
        'slug' => 'listado-albaranes-legacy',
    ]);

    $this->post(
        route('admin.modules.functionalities.store', $this->module),
        functionalityStorePayload(['name' => 'listado albaranes']),
    )->assertSessionHasErrors('name');
});

it('update solo cambia label y conserva name y slug', function () {
    $functionality = Functionality::factory()->create([
        'module_id' => $this->module->id,
        'name' => 'Fijo',
        'slug' => 'fijo-slug',
        'label' => ['es' => 'Viejo', 'en' => 'Old', 'ca' => ''],
        'status' => FunctionalityStatus::Active,
    ]);

    $this->put(route('admin.functionalities.update', $functionality), [
        'label' => ['es' => 'Nuevo', 'en' => 'New', 'ca' => 'Nou'],
        'name' => 'Hackeado',
        'slug' => 'hackeado',
        'status' => FunctionalityStatus::Inactive->value,
        'module_id' => $this->otherModule->id,
    ])->assertRedirect(route('admin.modules.edit', [
        'module' => $this->module,
        'tab' => 'functionalities',
    ]));

    $functionality->refresh();

    expect($functionality->name)->toBe('Fijo')
        ->and($functionality->slug)->toBe('fijo-slug')
        ->and($functionality->module_id)->toBe($this->module->id)
        ->and($functionality->status)->toBe(FunctionalityStatus::Active)
        ->and($functionality->getTranslation('label', 'es'))->toBe('Nuevo')
        ->and($functionality->getTranslation('label', 'en'))->toBe('New');
});

it('toggle status JSON sin recarga', function () {
    $functionality = Functionality::factory()->create([
        'module_id' => $this->module->id,
        'status' => FunctionalityStatus::Active,
    ]);

    $this->patchJson(route('admin.functionalities.status', $functionality), [
        'status' => 'inactive',
    ])
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    expect($functionality->refresh()->status)->toBe(FunctionalityStatus::Inactive);

    $this->patchJson(route('admin.functionalities.status', $functionality), [
        'status' => 'active',
    ])
        ->assertOk()
        ->assertJson(['status' => 'active']);

    expect($functionality->refresh()->status)->toBe(FunctionalityStatus::Active);
});

it('delete soft-deletea y conserva permisos Spatie', function () {
    $functionality = Functionality::factory()->create([
        'module_id' => $this->module->id,
        'slug' => 'borrable',
        'name' => 'Borrable',
    ]);

    Permission::findOrCreate('borrable.viewAny');
    Permission::findOrCreate('borrable.view');
    Permission::findOrCreate('borrable.create');
    Permission::findOrCreate('borrable.update');
    Permission::findOrCreate('borrable.delete');

    $this->delete(route('admin.functionalities.destroy', $functionality))
        ->assertRedirect(route('admin.modules.edit', [
            'module' => $this->module,
            'tab' => 'functionalities',
        ]));

    expect(Functionality::query()->whereKey($functionality->id)->exists())->toBeFalse()
        ->and(Functionality::withTrashed()->whereKey($functionality->id)->exists())->toBeTrue();

    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "borrable.{$ability}")->exists())->toBeTrue();
    }
});

it('niega table nested sin permiso', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->getJson(route('admin.modules.functionalities.table', $this->module))
        ->assertForbidden();
});

it('niega store nested sin permiso', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->post(
        route('admin.modules.functionalities.store', $this->module),
        functionalityStorePayload(),
    )->assertForbidden();
});
