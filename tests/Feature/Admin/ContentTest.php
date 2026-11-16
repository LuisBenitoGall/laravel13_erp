<?php

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Content;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\HelpContentMap;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    $this->otherCompany = Company::factory()->create();
    enableModuleForCompany($this->company, 'configuracion');
    enableModuleForCompany($this->otherCompany, 'configuracion');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    $this->user->companies()->attach([$this->company->id, $this->otherCompany->id]);
    actingWithCompany($this->user, $this->company);
});

it('Super Admin abre Index de contenidos', function () {
    $this->get(route('admin.contents.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Contents/Index'));
});

it('Create Continuar persiste Inactive y redirige a Edit placeholder', function () {
    $response = $this->post(route('admin.contents.store'), [
        'name' => 'Fecha de gasto',
        'kind' => 'help',
    ]);

    $content = Content::query()->firstOrFail();

    $response->assertRedirect(route('admin.contents.edit', $content));

    expect($content->company_id)->toBeNull()
        ->and($content->kind)->toBe(ContentKind::Help)
        ->and($content->status)->toBe(ContentStatus::Inactive)
        ->and($content->code)->toBe('fecha-de-gasto');

    $this->get(route('admin.contents.edit', $content))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contents/Edit')
            ->where('content.code', 'fecha-de-gasto')
            ->where('content.kind', 'help')
            ->where('content.status', 'inactive'));
});

it('Create admite kind Article', function () {
    $this->post(route('admin.contents.store'), [
        'name' => 'Guía editorial',
        'kind' => 'article',
    ])->assertRedirect();

    $content = Content::query()->firstOrFail();

    expect($content->kind)->toBe(ContentKind::Article)
        ->and($content->status)->toBe(ContentStatus::Inactive);
});

it('Create no muestra status en UI y Tipo usa RadioGroup', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Contents/Create.tsx'));

    expect($source)->toContain('kind')
        ->and($source)->toContain('continuar')
        ->and($source)->toContain('RadioGroup')
        ->and($source)->not->toContain('Select')
        ->and($source)->not->toContain('status')
        ->and($source)->not->toContain('Wysiwyg')
        ->and($source)->not->toContain('LangTabs');
});

it('Edit es placeholder sin formulario completo', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Contents/Edit.tsx'));

    expect($source)->toContain('contenido_edicion_proximamente')
        ->and($source)->not->toContain('Wysiwyg')
        ->and($source)->not->toContain('LangTabs')
        ->and($source)->not->toContain('useForm');
});

it('soft delete desde Index', function () {
    $content = Content::factory()->create();

    $this->delete(route('admin.contents.destroy', $content))->assertRedirect();

    expect(Content::query()->whereKey($content->id)->exists())->toBeFalse()
        ->and(Content::withTrashed()->whereKey($content->id)->exists())->toBeTrue();
});

it('Inactive no aparece en help_tips', function () {
    Content::factory()->create([
        'code' => 'inactive-tip',
        'status' => ContentStatus::Inactive,
        'title' => ['es' => 'No', 'en' => 'No', 'ca' => 'No'],
    ]);
    Content::factory()->active()->create([
        'code' => 'active-tip',
        'title' => ['es' => 'Sí', 'en' => 'Yes', 'ca' => 'Sí'],
    ]);

    $map = app(HelpContentMap::class)->forLocale('es');

    expect($map)->not->toHaveKey('inactive-tip')
        ->and($map)->toHaveKey('active-tip')
        ->and($map['active-tip']['title'])->toBe('Sí');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('help_tips.inactive-tip')
            ->where('help_tips.active-tip.title', 'Sí'));
});

it('mismo tip Active tras cambio de company', function () {
    Content::factory()->active()->create([
        'code' => 'global-tip',
        'title' => ['es' => 'Global', 'en' => 'Global', 'ca' => 'Global'],
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('help_tips.global-tip.title', 'Global'));

    actingWithCompany($this->user, $this->otherCompany);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('help_tips.global-tip.title', 'Global'));
});

it('sin contents.* las rutas responden 403', function () {
    $content = Content::factory()->create();
    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.contents.index'))->assertForbidden();
    $this->get(route('admin.contents.create'))->assertForbidden();
    $this->post(route('admin.contents.store'), [
        'name' => 'X',
        'kind' => 'help',
    ])->assertForbidden();
    $this->get(route('admin.contents.edit', $content))->assertForbidden();
    $this->delete(route('admin.contents.destroy', $content))->assertForbidden();
});

it('seeder y menú Contenidos', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "contents.{$ability}")->exists())->toBeTrue();
    }

    $menu = app(MenuBuilder::class)->forUser($this->user);
    $config = collect($menu)->firstWhere('slug', 'configuracion');
    $item = collect($config['functionalities'] ?? [])->firstWhere('slug', 'contents');

    expect($item)->not->toBeNull()
        ->and($item['href'])->toBe('/admin/contents');
});

it('HelpTip silencia codes inexistentes y no usa JSON runtime', function () {
    $source = (string) file_get_contents(resource_path('js/Components/HelpTip.tsx'));

    expect($source)->toContain('help_tips')
        ->and($source)->toContain('return null')
        ->and(file_exists(storage_path('json/contents.json')))->toBeFalse()
        ->and(Cache::get('contents.help.map.es'))->toBeNull();
});
