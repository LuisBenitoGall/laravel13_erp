<?php

use App\Domains\Admin\Actions\RevokeModuleAuthorizationForCompany;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
use App\Domains\Admin\Enums\FunctionalityStatus;
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

it('el seeder registra families bajo products con permisos', function () {
    $this->seed(AdminCatalogSeeder::class);

    $module = Module::query()->where('slug', 'products')->firstOrFail();

    expect(Functionality::query()->where('module_id', $module->id)->pluck('slug')->all())
        ->toContain('catalogue')
        ->toContain('families')
        ->and(Functionality::query()->where('slug', 'families')->where('status', '!=', FunctionalityStatus::Active)->exists())->toBeFalse();

    $names = Permission::query()->pluck('name');

    foreach (familiesPermissions() as $permission) {
        expect($names)->toContain($permission);
    }
});

it('menú Catálogo agrupa Artículos y Familias', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::query()->where('slug', 'acme')->firstOrFail();
    $module = Module::query()->where('slug', 'products')->firstOrFail();

    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $company);

    app(RevokeModuleAuthorizationForCompany::class)->handle($company, $module);
    expect(collect(app(MenuBuilder::class)->forUser($sa))->pluck('slug'))->not->toContain('products');

    enableModuleForCompany($company, 'products');

    $entry = collect(app(MenuBuilder::class)->forUser($sa))->firstWhere('slug', 'products');
    $items = collect($entry['functionalities'] ?? [])->keyBy('slug');

    $locale = app()->getLocale();
    $moduleLabel = $module->fresh()->getTranslation('label', $locale)
        ?: $module->getTranslation('label', 'es')
        ?: 'Catálogo';
    $catalogue = Functionality::query()->where('slug', 'catalogue')->firstOrFail();
    $families = Functionality::query()->where('slug', 'families')->firstOrFail();
    $catalogueLabel = $catalogue->getTranslation('label', $locale)
        ?: $catalogue->getTranslation('label', 'es')
        ?: 'Artículos';
    $familiesLabel = $families->getTranslation('label', $locale)
        ?: $families->getTranslation('label', 'es')
        ?: 'Familias';

    $expectedModuleLabel = ['es' => 'Catálogo', 'en' => 'Catalogue', 'ca' => 'Catàleg'][$locale] ?? 'Catálogo';
    $expectedCatalogueLabel = ['es' => 'Artículos', 'en' => 'Articles', 'ca' => 'Articles'][$locale] ?? 'Artículos';
    $expectedFamiliesLabel = ['es' => 'Familias', 'en' => 'Families', 'ca' => 'Famílies'][$locale] ?? 'Familias';

    expect($entry['label'])->toBe($moduleLabel)
        ->and($moduleLabel)->toBe($expectedModuleLabel)
        ->and($items->has('catalogue'))->toBeTrue()
        ->and($items->has('families'))->toBeTrue()
        ->and($items['catalogue']['label'])->toBe($catalogueLabel)
        ->and($catalogueLabel)->toBe($expectedCatalogueLabel)
        ->and($items['catalogue']['href'])->toBe(route('products.index', absolute: false))
        ->and($items['families']['label'])->toBe($familiesLabel)
        ->and($familiesLabel)->toBe($expectedFamiliesLabel)
        ->and($items['families']['href'])->toBe(route('products.families.index', absolute: false));

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(['products.access', 'catalogue.view', 'families.view']);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $entry = collect(app(MenuBuilder::class)->forUser($user))->firstWhere('slug', 'products');
    expect(collect($entry['functionalities'])->pluck('slug')->all())
        ->toContain('catalogue')
        ->toContain('families');

    $user->revokePermissionTo('families.view');
    $user->unsetRelation('permissions');

    $entry = collect(app(MenuBuilder::class)->forUser($user))->firstWhere('slug', 'products');
    expect(collect($entry['functionalities'])->pluck('slug')->all())
        ->toContain('catalogue')
        ->not->toContain('families');
});

it('manager de company autorizada recibe permisos families.*', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::factory()->create();
    enableModuleForCompany($company, 'products');
    app(SyncManagerPermissionsForCompany::class)->handle($company);

    setPermissionsTeamId($company->id);
    $manager = Role::query()->where('name', 'manager')->where('company_id', $company->id)->firstOrFail();

    foreach (familiesPermissions() as $permission) {
        expect($manager->hasPermissionTo($permission))->toBeTrue();
    }
});

dataset('families routes', [
    'index' => ['products.families.index', 'families.view'],
    'table' => ['products.families.table', 'families.view'],
    'create' => ['products.families.create', 'families.create'],
]);

it('V-21 bloquea familias si products no está autorizado', function (string $route) {
    ensureFamiliesCatalog();
    $company = Company::factory()->create();

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(collect(familiesPermissions())->map(fn ($n) => Permission::findOrCreate($n))->all());

    $this->get(route($route))->assertForbidden();
})->with('families routes');

it('V-21 bloquea familias si products está Inactive', function (string $route) {
    $company = Company::factory()->create();
    actingFamiliesUser($company);

    Module::query()->where('slug', 'products')->update(['status' => ModuleStatus::Inactive]);

    $this->get(route($route))->assertForbidden();
})->with('families routes');

it('V-21 niega familias sin permiso de Functionality', function (string $route, string $permission) {
    $company = Company::factory()->create();
    $user = actingFamiliesUser($company);

    $user->revokePermissionTo($permission);
    $user->unsetRelation('permissions');

    $this->get(route($route))->assertForbidden();
})->with('families routes');

it('V-21 permite familias con capas y permiso', function (string $route) {
    $company = Company::factory()->create();
    actingFamiliesUser($company);

    $this->get(route($route))->assertOk();
})->with('families routes');
