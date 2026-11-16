<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Preference;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\PreferenceModuleResolver;
use App\Domains\Admin\Support\PreferenceUrlNormalizer;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    $this->otherCompany = Company::factory()->create();

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    $this->user->companies()->attach([$this->company->id, $this->otherCompany->id]);

    actingWithCompany($this->user, $this->company);
});

it('normaliza url sin host ni hash', function () {
    expect(PreferenceUrlNormalizer::normalize('https://erp.test/admin/companies?tab=info#x'))
        ->toBe('/admin/companies?tab=info')
        ->and(PreferenceUrlNormalizer::normalize('admin/workplaces'))
        ->toBe('/admin/workplaces');
});

it('rechaza protocol-relative y paths fuera de prefijos app', function () {
    expect(PreferenceUrlNormalizer::isAllowedAppPath(PreferenceUrlNormalizer::normalize('//evil.com/phish')))
        ->toBeFalse()
        ->and(PreferenceUrlNormalizer::isAllowedAppPath('/logout'))->toBeFalse()
        ->and(PreferenceUrlNormalizer::isAllowedAppPath('/admin/workplaces'))->toBeTrue()
        ->and(PreferenceUrlNormalizer::isAllowedAppPath('/dashboard'))->toBeTrue()
        ->and(PreferenceUrlNormalizer::isAllowedAppPath('/account'))->toBeTrue();

    $this->post(route('admin.preferences.store'), [
        'name' => 'Phish',
        'url' => '//evil.com/phish',
    ])->assertSessionHasErrors('url');

    $this->post(route('admin.preferences.store'), [
        'name' => 'Logout',
        'url' => '/logout',
    ])->assertSessionHasErrors('url');

    expect(Preference::query()->where('user_id', $this->user->id)->count())->toBe(0);

    $this->post(route('admin.preferences.store'), [
        'name' => 'Empresas',
        'url' => 'https://erp.test/admin/companies?x=1',
    ])->assertRedirect();

    expect(Preference::query()->where('user_id', $this->user->id)->value('url'))
        ->toBe('/admin/companies?x=1');
});

it('resuelve module_id por longest prefix del menú', function () {
    $resolver = app(PreferenceModuleResolver::class);
    $moduleId = $resolver->resolve('/admin/companies/1/edit');

    $empresas = Module::query()->where('slug', 'empresas')->value('id');

    expect($moduleId)->toBe((int) $empresas)
        ->and($resolver->resolve('/ruta/desconocida'))->toBeNull()
        ->and($resolver->resolve('/admin/companies'))->toBe((int) $empresas);
});

it('guarda favorito de la vista actual para user+company', function () {
    $this->post(route('admin.preferences.store'), [
        'name' => 'Mis empresas',
        'url' => 'https://example.test/admin/companies?page=1',
    ])->assertRedirect();

    $preference = Preference::query()->where('user_id', $this->user->id)->firstOrFail();

    expect($preference->name)->toBe('Mis empresas')
        ->and($preference->url)->toBe('/admin/companies?page=1')
        ->and($preference->company_id)->toBe($this->company->id)
        ->and($preference->module_id)->not->toBeNull();
});

it('re-guardar la misma url actualiza el nombre', function () {
    $this->post(route('admin.preferences.store'), [
        'name' => 'Uno',
        'url' => '/admin/workplaces',
    ])->assertRedirect();

    $this->post(route('admin.preferences.store'), [
        'name' => 'Dos',
        'url' => '/admin/workplaces',
    ])->assertRedirect();

    expect(Preference::query()->where('user_id', $this->user->id)->count())->toBe(1)
        ->and(Preference::query()->where('user_id', $this->user->id)->value('name'))->toBe('Dos');
});

it('bloquea el tope de 20 favoritos por user+company', function () {
    Preference::factory()->count(Preference::MAX_PER_COMPANY)->create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
    ]);

    $this->post(route('admin.preferences.store'), [
        'name' => 'Extra',
        'url' => '/admin/extra-unique',
    ])->assertSessionHasErrors('name');

    expect(Preference::query()->where('user_id', $this->user->id)->count())
        ->toBe(Preference::MAX_PER_COMPANY);
});

it('dashboard lista solo favoritos de la company de sesión', function () {
    Preference::factory()->create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'name' => 'De A',
        'url' => '/admin/a',
    ]);
    Preference::factory()->create([
        'user_id' => $this->user->id,
        'company_id' => $this->otherCompany->id,
        'name' => 'De B',
        'url' => '/admin/b',
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('preferences', 1)
            ->where('preferences.0.name', 'De A'));

    actingWithCompany($this->user, $this->otherCompany);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('preferences', 1)
            ->where('preferences.0.name', 'De B'));
});

it('elimina favorito propio y aísla destroy cruzado', function () {
    $owner = User::factory()->create();
    $owner->companies()->attach([$this->company->id, $this->otherCompany->id]);
    actingWithCompany($owner, $this->company);

    $own = Preference::factory()->create([
        'user_id' => $owner->id,
        'company_id' => $this->company->id,
        'url' => '/admin/own',
    ]);

    $otherUser = User::factory()->create();
    $otherUser->companies()->attach($this->company->id);
    $foreign = Preference::factory()->create([
        'user_id' => $otherUser->id,
        'company_id' => $this->company->id,
        'url' => '/admin/foreign',
    ]);

    $this->delete(route('admin.preferences.destroy', $own))->assertRedirect();
    expect(Preference::query()->whereKey($own->id)->exists())->toBeFalse();

    $this->delete(route('admin.preferences.destroy', $foreign))->assertForbidden();

    actingWithCompany($owner, $this->otherCompany);
    $otherCompanyPref = Preference::factory()->create([
        'user_id' => $owner->id,
        'company_id' => $this->otherCompany->id,
        'url' => '/admin/other-co',
    ]);

    actingWithCompany($owner, $this->company);
    $this->delete(route('admin.preferences.destroy', $otherCompanyPref))->assertNotFound();
});

it('FavoritesPopover sustituye el stub vacío', function () {
    $source = (string) file_get_contents(resource_path('js/Components/Topbar/FavoritesPopover.tsx'));
    $topbar = (string) file_get_contents(resource_path('js/Components/Topbar/AppTopbar.tsx'));

    expect($source)->toContain('mis_opciones_preferidas')
        ->and($source)->toContain('/admin/preferences')
        ->and($topbar)->toContain('FavoritesPopover')
        ->and($topbar)->not->toContain('FavoritesStub')
        ->and(file_exists(resource_path('js/Components/Topbar/FavoritesStub.tsx')))->toBeFalse();
});
