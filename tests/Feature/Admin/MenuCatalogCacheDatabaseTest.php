<?php

use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    // Reproduce el store de local/prod (phpunit.xml fuerza array).
    config(['cache.default' => 'database']);
    MenuCatalogCache::forget();
});

it('warm+hit en cache database devuelve payload plano usable por MenuBuilder', function () {
    $company = Company::factory()->create();

    app(CreateModule::class)->handle([
        'name' => 'Dashboard',
        'label' => ['es' => 'Dashboard', 'en' => 'Dashboard'],
        'color' => '#0f172a',
        'icon' => 'home',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    enableModuleForCompany($company, 'dashboard');
    withCurrentCompany($company);

    MenuCatalogCache::forget();

    $warm = MenuCatalogCache::modules();
    expect($warm)->toBeInstanceOf(Collection::class)
        ->and($warm->first())->toBeArray()
        ->and($warm->first())->toHaveKeys(['id', 'slug', 'label', 'color', 'icon', 'level', 'functionalities'])
        ->and($warm->pluck('slug')->all())->toContain('dashboard')
        ->and($warm->first()['label'])->toBeArray();

    $raw = Cache::store('database')->get(MenuCatalogCache::KEY);
    expect($raw)->toBeArray()
        ->and($raw)->not->toBeInstanceOf(__PHP_Incomplete_Class::class);

    $hit = MenuCatalogCache::modules();
    expect($hit)->toBeInstanceOf(Collection::class)
        ->and($hit->first())->toBeArray()
        ->and($hit->pluck('slug')->all())->toContain('dashboard')
        ->and($hit->count())->toBe($warm->count());

    $user = User::factory()->create();
    Permission::findOrCreate('dashboard.access');
    $user->givePermissionTo('dashboard.access');

    $menu = app(MenuBuilder::class)->forUser($user);
    $dash = collect($menu)->firstWhere('slug', 'dashboard');

    expect($dash)->not->toBeNull()
        ->and($dash['href'] ?? null)->toBe(route('dashboard', absolute: false));
});

it('Inertia dashboard tras warm+hit bajo cache database no TypeError', function () {
    $company = Company::factory()->create();

    app(CreateModule::class)->handle([
        'name' => 'Dashboard',
        'label' => ['es' => 'Dashboard'],
        'color' => '#0f172a',
        'icon' => 'home',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    enableModuleForCompany($company, 'dashboard');

    Role::findOrCreate(Role::SUPER_ADMIN);
    $user = User::factory()->create();
    $user->companies()->attach($company->id);
    setPermissionsTeamId(null);
    $user->assignRole(Role::SUPER_ADMIN);

    MenuCatalogCache::forget();
    MenuCatalogCache::modules(); // warm
    MenuCatalogCache::modules(); // hit

    actingWithCompany($user, $company);

    $this->get(route('dashboard'))
        ->assertOk();
});
