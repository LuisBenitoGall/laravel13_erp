<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\MenuBuilder;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    MenuCatalogCache::forget();

    Role::findOrCreate(Role::SUPER_ADMIN);

    $this->company = Company::factory()->create(['status' => CompanyStatus::Active]);

    $this->cuenta = app(CreateModule::class)->handle([
        'name' => 'Cuenta',
        'label' => ['es' => 'Cuenta'],
        'color' => '#7c3aed',
        'icon' => 'user',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->cuenta->id,
        'name' => 'Profile',
        'slug' => 'profile',
        'label' => ['es' => 'Mi cuenta'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->cuenta->id,
        'name' => 'Company modules',
        'slug' => 'company-modules',
        'label' => ['es' => 'Mis módulos'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'cuenta');
    MenuCatalogCache::forget();
});

it('expone account.show y MenuBuilder profile no apunta a /profile', function () {
    expect(Route::has('account.show'))->toBeTrue()
        ->and(Route::has('profile.show'))->toBeTrue();

    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::findOrCreate('cuenta.access'),
        Permission::findOrCreate('profile.view'),
        Permission::findOrCreate('company-modules.view'),
    ]);
    withCurrentCompany($this->company);

    $menu = app(MenuBuilder::class)->forUser($user);
    $cuenta = collect($menu)->firstWhere('slug', 'cuenta');
    $profileItem = collect($cuenta['functionalities'] ?? [])->firstWhere('slug', 'profile');
    $modulesItem = collect($cuenta['functionalities'] ?? [])->firstWhere('slug', 'company-modules');

    $hrefs = collect($cuenta['functionalities'] ?? [])->pluck('href')->all();

    expect($profileItem)->not->toBeNull()
        ->and($profileItem['label'])->toBe(__('mi_cuenta'))
        ->and($profileItem['href'])->toBe(route('account.show', absolute: false))
        ->and($profileItem['href'])->not->toBe(route('profile.show', absolute: false))
        ->and($modulesItem['href'])->toBe(route('admin.company-modules.index', absolute: false))
        ->and($hrefs)->not->toContain('/profile')
        ->and($hrefs)->not->toContain(route('profile.show', absolute: false));
});

it('GET /account muestra placeholder proximamente autorizado', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->get(route('account.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Account/ComingSoon'));
});

it('AsideNav fuerza sin activo en /profile y avatar sigue a /profile', function () {
    $aside = file_get_contents(resource_path('js/Components/AsideNav.tsx'));
    $menu = file_get_contents(resource_path('js/Components/Topbar/UserMenu.tsx'));
    $edit = file_get_contents(resource_path('js/Pages/Users/Edit.tsx'));

    expect($aside)->toContain('isUserProfilePath')
        ->and($aside)->toContain('/profile/')
        ->and($menu)->toContain('href="/profile"')
        ->and($menu)->toContain("t('perfil')")
        ->and($edit)->toContain("optionKey: 'perfil'")
        ->and($edit)->not->toMatch("/context === 'profile'\\s*\\?[\\s\\S]*optionKey: 'mi_cuenta'/");
});
