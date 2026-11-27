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

it('el seeder registra tags bajo products con permisos', function () {
    $this->seed(AdminCatalogSeeder::class);

    $module = Module::query()->where('slug', 'products')->firstOrFail();

    expect(Functionality::query()->where('module_id', $module->id)->pluck('slug')->all())
        ->toContain('catalogue')
        ->toContain('families')
        ->toContain('categories')
        ->toContain('tags')
        ->and(Functionality::query()->where('slug', 'tags')->where('status', '!=', FunctionalityStatus::Active)->exists())->toBeFalse();

    $names = Permission::query()->pluck('name');

    foreach (tagsPermissions() as $permission) {
        expect($names)->toContain($permission);
    }
});

it('menú Catálogo incluye Etiquetas con href /products/tags', function () {
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
    $tags = Functionality::query()->where('slug', 'tags')->firstOrFail();
    $expectedLabel = ['es' => 'Etiquetas', 'en' => 'Tags', 'ca' => 'Etiquetes'][$locale] ?? 'Etiquetas';
    $tagsLabel = $tags->getTranslation('label', $locale)
        ?: $tags->getTranslation('label', 'es')
        ?: 'Etiquetas';

    expect($items->has('tags'))->toBeTrue()
        ->and($items['tags']['label'])->toBe($tagsLabel)
        ->and($tagsLabel)->toBe($expectedLabel)
        ->and($items['tags']['href'])->toBe(route('products.tags.index', absolute: false));

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(['products.access', 'catalogue.view']);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $entry = collect(app(MenuBuilder::class)->forUser($user))->firstWhere('slug', 'products');
    expect(collect($entry['functionalities'])->pluck('slug')->all())
        ->toContain('catalogue')
        ->not->toContain('tags');
});

it('manager recibe permisos tags.*', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::factory()->create();
    enableModuleForCompany($company, 'products');
    app(SyncManagerPermissionsForCompany::class)->handle($company);

    setPermissionsTeamId($company->id);
    $manager = Role::query()->where('name', 'manager')->where('company_id', $company->id)->firstOrFail();

    foreach (tagsPermissions() as $permission) {
        expect($manager->hasPermissionTo($permission))->toBeTrue();
    }
});

dataset('tags routes', [
    'index' => ['products.tags.index', 'tags.view'],
    'table' => ['products.tags.table', 'tags.view'],
    'create' => ['products.tags.create', 'tags.create'],
]);

it('V-21 bloquea tags si products no está autorizado', function (string $route) {
    ensureTagsCatalog();
    $company = Company::factory()->create();

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(collect(tagsPermissions())->map(fn ($n) => Permission::findOrCreate($n))->all());

    $this->get(route($route))->assertForbidden();
})->with('tags routes');

it('V-21 bloquea tags si products está Inactive', function (string $route) {
    $company = Company::factory()->create();
    actingTagsUser($company);

    Module::query()->where('slug', 'products')->update(['status' => ModuleStatus::Inactive]);

    $this->get(route($route))->assertForbidden();
})->with('tags routes');

it('V-21 niega tags sin permiso', function (string $route, string $permission) {
    $company = Company::factory()->create();
    $user = actingTagsUser($company);

    $user->revokePermissionTo($permission);
    $user->unsetRelation('permissions');

    $this->get(route($route))->assertForbidden();
})->with('tags routes');

it('V-21 permite tags con capas y permiso', function (string $route) {
    $company = Company::factory()->create();
    actingTagsUser($company);

    $this->get(route($route))->assertOk();
})->with('tags routes');
