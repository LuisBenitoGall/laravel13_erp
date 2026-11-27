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

it('el seeder registra categories bajo products con permisos', function () {
    $this->seed(AdminCatalogSeeder::class);

    $module = Module::query()->where('slug', 'products')->firstOrFail();

    expect(Functionality::query()->where('module_id', $module->id)->pluck('slug')->all())
        ->toContain('catalogue')
        ->toContain('families')
        ->toContain('categories')
        ->and(Functionality::query()->where('slug', 'categories')->where('status', '!=', FunctionalityStatus::Active)->exists())->toBeFalse();

    $names = Permission::query()->pluck('name');

    foreach (categoriesPermissions() as $permission) {
        expect($names)->toContain($permission);
    }
});

it('menú Catálogo incluye Categorías con href /products/categories', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::query()->where('slug', 'acme')->firstOrFail();
    $module = Module::query()->where('slug', 'products')->firstOrFail();

    $sa = User::factory()->create();
    $sa->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($sa, $company);

    app(RevokeModuleAuthorizationForCompany::class)->handle($company, $module);
    enableModuleForCompany($company, 'products');

    $entry = collect(app(MenuBuilder::class)->forUser($sa))->firstWhere('slug', 'products');
    $items = collect($entry['functionalities'] ?? [])->keyBy('slug');

    $locale = app()->getLocale();
    $categories = Functionality::query()->where('slug', 'categories')->firstOrFail();
    $expectedLabel = ['es' => 'Categorías', 'en' => 'Categories', 'ca' => 'Categories'][$locale] ?? 'Categorías';
    $categoriesLabel = $categories->getTranslation('label', $locale)
        ?: $categories->getTranslation('label', 'es')
        ?: 'Categorías';

    expect($items->has('categories'))->toBeTrue()
        ->and($items['categories']['label'])->toBe($categoriesLabel)
        ->and($categoriesLabel)->toBe($expectedLabel)
        ->and($items['categories']['href'])->toBe(route('products.categories.index', absolute: false));

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(['products.access', 'catalogue.view']);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $entry = collect(app(MenuBuilder::class)->forUser($user))->firstWhere('slug', 'products');
    expect(collect($entry['functionalities'])->pluck('slug')->all())
        ->toContain('catalogue')
        ->not->toContain('categories');
});

it('manager recibe permisos categories.*', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::factory()->create();
    enableModuleForCompany($company, 'products');
    app(SyncManagerPermissionsForCompany::class)->handle($company);

    setPermissionsTeamId($company->id);
    $manager = Role::query()->where('name', 'manager')->where('company_id', $company->id)->firstOrFail();

    foreach (categoriesPermissions() as $permission) {
        expect($manager->hasPermissionTo($permission))->toBeTrue();
    }
});

dataset('categories routes', [
    'index' => ['products.categories.index', 'categories.view'],
    'table' => ['products.categories.table', 'categories.view'],
    'create' => ['products.categories.create', 'categories.create'],
]);

it('V-21 bloquea categories si products no está autorizado', function (string $route) {
    ensureCategoriesCatalog();
    $company = Company::factory()->create();

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(collect(categoriesPermissions())->map(fn ($n) => Permission::findOrCreate($n))->all());

    $this->get(route($route))->assertForbidden();
})->with('categories routes');

it('V-21 bloquea categories si products está Inactive', function (string $route) {
    $company = Company::factory()->create();
    actingCategoriesUser($company);

    Module::query()->where('slug', 'products')->update(['status' => ModuleStatus::Inactive]);

    $this->get(route($route))->assertForbidden();
})->with('categories routes');

it('V-21 niega categories sin permiso', function (string $route, string $permission) {
    $company = Company::factory()->create();
    $user = actingCategoriesUser($company);

    $user->revokePermissionTo($permission);
    $user->unsetRelation('permissions');

    $this->get(route($route))->assertForbidden();
})->with('categories routes');

it('V-21 permite categories con capas y permiso', function (string $route) {
    $company = Company::factory()->create();
    actingCategoriesUser($company);

    $this->get(route($route))->assertOk();
})->with('categories routes');
