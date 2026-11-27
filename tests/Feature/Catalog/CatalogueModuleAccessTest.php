<?php

use App\Domains\Admin\Actions\RevokeModuleAuthorizationForCompany;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/CatalogTesting.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);
});

it('el seeder registra Module products Optional Active con catalogue y permisos', function () {
    $this->seed(AdminCatalogSeeder::class);

    $module = Module::query()->where('slug', 'products')->firstOrFail();

    expect($module->level)->toBe(ModuleLevel::Optional)
        ->and($module->status)->toBe(ModuleStatus::Active)
        ->and(Functionality::query()->where('module_id', $module->id)->pluck('slug')->all())
        ->toContain('catalogue')
        ->and(Functionality::query()->where('slug', 'catalogue')->where('status', '!=', FunctionalityStatus::Active)->exists())->toBeFalse();

    $names = Permission::query()->pluck('name');

    foreach (cataloguePermissions() as $permission) {
        expect($names)->toContain($permission);
    }
});

it('menú Productos → Catálogo: capas Optional + comodín + catalogue.view; href /products', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::query()->where('slug', 'acme')->firstOrFail();
    $module = Module::query()->where('slug', 'products')->firstOrFail();

    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $company);

    // Sin autorizar/encender: no aparece (Optional exige autorizado ∩ encendido).
    app(RevokeModuleAuthorizationForCompany::class)->handle($company, $module);

    expect(collect(app(MenuBuilder::class)->forUser($sa))->pluck('slug'))->not->toContain('products');

    enableModuleForCompany($company, 'products');

    $entry = collect(app(MenuBuilder::class)->forUser($sa))->firstWhere('slug', 'products');
    $items = collect($entry['functionalities'] ?? [])->keyBy('slug');

    $locale = app()->getLocale();
    $moduleLabel = $module->getTranslation('label', $locale)
        ?: $module->getTranslation('label', 'es')
        ?: 'Productos';
    $catalogue = Functionality::query()->where('slug', 'catalogue')->firstOrFail();
    $catalogueLabel = $catalogue->getTranslation('label', $locale)
        ?: $catalogue->getTranslation('label', 'es')
        ?: 'Catálogo';

    expect($entry['label'])->toBe($moduleLabel)
        ->and($items->has('catalogue'))->toBeTrue()
        ->and($items['catalogue']['label'])->toBe($catalogueLabel)
        ->and($items['catalogue']['href'])->toBe(route('products.index', absolute: false));

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(['products.access', 'catalogue.view']);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $entry = collect(app(MenuBuilder::class)->forUser($user))->firstWhere('slug', 'products');

    expect(collect($entry['functionalities'])->pluck('slug')->all())->toBe(['catalogue']);

    $user->revokePermissionTo('products.access');
    $user->unsetRelation('permissions');

    expect(collect(app(MenuBuilder::class)->forUser($user))->pluck('slug'))->not->toContain('products');
});

it('manager de company autorizada recibe permisos de products/catalogue', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::factory()->create();
    enableModuleForCompany($company, 'products');
    app(SyncManagerPermissionsForCompany::class)->handle($company);

    setPermissionsTeamId($company->id);
    $manager = Role::query()->where('name', 'manager')->where('company_id', $company->id)->firstOrFail();

    foreach (cataloguePermissions() as $permission) {
        expect($manager->hasPermissionTo($permission))->toBeTrue();
    }
});

dataset('catalogue routes', [
    'index' => ['products.index', 'catalogue.view'],
    'table' => ['products.table', 'catalogue.view'],
    'create' => ['products.create', 'catalogue.create'],
]);

it('V-21 bloquea si catalogue no está autorizado para la company', function (string $route) {
    ensureCatalogueCatalog();
    $company = Company::factory()->create();

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(collect(cataloguePermissions())->map(fn ($n) => Permission::findOrCreate($n))->all());

    $this->get(route($route))->assertForbidden();
})->with('catalogue routes');

it('V-21 bloquea si el catálogo catalogue está Inactive (bloqueo global)', function (string $route) {
    $company = Company::factory()->create();
    actingCatalogueUser($company);

    Module::query()->where('slug', 'products')->update(['status' => ModuleStatus::Inactive]);

    $this->get(route($route))->assertForbidden();
})->with('catalogue routes');

it('V-21 niega acceso sin permiso de products', function (string $route, string $permission) {
    $company = Company::factory()->create();
    $user = actingCatalogueUser($company);

    $user->revokePermissionTo($permission);
    $user->unsetRelation('permissions');

    $this->get(route($route))->assertForbidden();
})->with('catalogue routes');

it('V-21 permite acceso con capas satisfechas y permiso', function (string $route) {
    $company = Company::factory()->create();
    actingCatalogueUser($company);

    $this->get(route($route))->assertOk();
})->with('catalogue routes');
