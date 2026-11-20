<?php

use App\Domains\Admin\Actions\DisableModuleForCompany;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\MediaAsset;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    Storage::fake('public');

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

it('Super Admin abre Index de galería en grid', function () {
    MediaAsset::factory()->create(['name' => 'Logo']);

    $this->get(route('admin.gallery.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Gallery/Index')
            ->has('assets', 1)
            ->where('assets.0.name', 'Logo')
            ->has('maxKilobytes')
            ->where('maxPerUpload', 20)
            ->where('can.create', true)
            ->where('can.update', true)
            ->where('can.delete', true));
});

it('store múltiple con company_id null persiste y expone url', function () {
    $files = [
        UploadedFile::fake()->image('banner.png', 10, 10),
        UploadedFile::fake()->image('logo.jpg', 8, 8),
    ];

    $this->post(route('admin.gallery.store'), [
        'images' => $files,
    ])->assertRedirect(route('admin.gallery.index'));

    $assets = MediaAsset::query()->orderBy('name')->get();

    expect($assets)->toHaveCount(2)
        ->and($assets->every(fn (MediaAsset $a) => $a->company_id === null))->toBeTrue()
        ->and($assets->pluck('name')->all())->toBe(['banner', 'logo'])
        ->and(Storage::disk('public')->exists($assets[0]->path))->toBeTrue()
        ->and(Storage::disk('public')->exists($assets[1]->path))->toBeTrue();
});

it('store con company_id responde error de validación', function () {
    $file = UploadedFile::fake()->image('x.png', 8, 8);

    $this->post(route('admin.gallery.store'), [
        'images' => [$file],
        'company_id' => $this->company->id,
    ])->assertSessionHasErrors('company_id');

    expect(MediaAsset::query()->count())->toBe(0);
});

it('update name y alt', function () {
    $asset = MediaAsset::factory()->create(['name' => 'Old', 'alt' => 'a']);

    $this->put(route('admin.gallery.update', $asset), [
        'name' => 'New',
        'alt' => 'nuevo alt',
    ])->assertRedirect(route('admin.gallery.index'));

    expect($asset->refresh()->name)->toBe('New')
        ->and($asset->alt)->toBe('nuevo alt');
});

it('delete soft-delete y quita fichero', function () {
    $asset = MediaAsset::factory()->create();
    $path = $asset->path;

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $this->delete(route('admin.gallery.destroy', $asset))
        ->assertRedirect(route('admin.gallery.index'));

    expect(MediaAsset::query()->whereKey($asset->id)->exists())->toBeFalse()
        ->and(MediaAsset::withTrashed()->whereKey($asset->id)->exists())->toBeTrue()
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});

it('rechaza mime o peso inválido', function () {
    $this->post(route('admin.gallery.store'), [
        'images' => [UploadedFile::fake()->create('doc.txt', 10, 'text/plain')],
    ])->assertSessionHasErrors('images.0');

    $big = UploadedFile::fake()->image('big.jpg')->size(3000);

    $this->post(route('admin.gallery.store'), [
        'images' => [$big],
    ])->assertSessionHasErrors('images.0');
});

it('sin gallery.* las rutas responden 403', function () {
    $asset = MediaAsset::factory()->create();
    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.gallery.index'))->assertForbidden();
    $this->post(route('admin.gallery.store'), [
        'images' => [UploadedFile::fake()->image('x.png')],
    ])->assertForbidden();
    $this->put(route('admin.gallery.update', $asset), [
        'name' => 'X',
        'alt' => '',
    ])->assertForbidden();
    $this->delete(route('admin.gallery.destroy', $asset))->assertForbidden();
});

it('aisla MediaAsset de company B (R-TEN-05)', function () {
    $assetB = MediaAsset::factory()->forCompany($this->otherCompany)->create(['name' => 'B-only']);

    $actorA = User::factory()->create();
    $actorA->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $actorA->givePermissionTo([
        'configuracion.access',
        'gallery.view',
        'gallery.viewAny',
        'gallery.create',
        'gallery.update',
        'gallery.delete',
    ]);
    actingWithCompany($actorA, $this->company);

    $this->get(route('admin.gallery.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('assets', fn ($assets) => collect($assets)->pluck('name')->doesntContain('B-only')));

    $this->put(route('admin.gallery.update', $assetB), [
        'name' => 'Hacked',
        'alt' => '',
    ])->assertForbidden();

    $this->delete(route('admin.gallery.destroy', $assetB))->assertForbidden();

    expect($assetB->refresh()->name)->toBe('B-only');
});

it('bloquea Index si configuracion no está activo para no-SA (V-21)', function () {
    $actor = User::factory()->create();
    $actor->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $actor->givePermissionTo(['configuracion.access', 'gallery.view']);
    actingWithCompany($actor, $this->company);

    $module = Module::query()->where('slug', 'configuracion')->firstOrFail();
    app(DisableModuleForCompany::class)->handle($this->company, $module);

    $this->get(route('admin.gallery.index'))->assertForbidden();
});

it('permite acceso con capas y permiso gallery (V-21)', function () {
    $actor = User::factory()->create();
    $actor->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $actor->givePermissionTo(['configuracion.access', 'gallery.view']);
    actingWithCompany($actor, $this->company);

    $this->get(route('admin.gallery.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('can.create', false)
            ->where('can.update', false)
            ->where('can.delete', false));
});

it('seeder y menú Galería tras Contenidos', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "gallery.{$ability}")->exists())->toBeTrue();
    }

    $menu = app(MenuBuilder::class)->forUser($this->user);
    $config = collect($menu)->firstWhere('slug', 'configuracion');
    $slugs = collect($config['functionalities'] ?? [])->pluck('slug')->values();
    $contentsIdx = $slugs->search('contents');
    $galleryIdx = $slugs->search('gallery');

    expect($galleryIdx)->not->toBeFalse()
        ->and($contentsIdx)->not->toBeFalse()
        ->and($galleryIdx)->toBe($contentsIdx + 1);

    $item = collect($config['functionalities'] ?? [])->firstWhere('slug', 'gallery');
    expect($item['href'])->toBe('/admin/gallery');
});

it('Index no usa ServerTable y no muestra Volver', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Gallery/Index.tsx'));

    expect($source)->toContain('lg:grid-cols-6')
        ->and($source)->toContain('showBack={false}')
        ->and($source)->toContain('multiple')
        ->and($source)->toContain('galeria_recortar')
        ->and($source)->toContain('CropDialog')
        ->and($source)->not->toContain('ServerTable');
});

it('CropDialog muestra toast.error i18n si falla el crop cliente', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Gallery/CropDialog.tsx'));

    expect($source)->toContain('toast.error')
        ->and($source)->toContain('galeria_recortar_error');
});

it('CropDialog muestra ayuda y selectores de ratio 1:1 4:3 16:9 libre', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Gallery/CropDialog.tsx'));

    expect($source)->toContain('galeria_recortar_ayuda')
        ->and($source)->toContain('galeria_ratio_1_1')
        ->and($source)->toContain('galeria_ratio_4_3')
        ->and($source)->toContain('galeria_ratio_16_9')
        ->and($source)->toContain('galeria_ratio_libre')
        ->and($source)->toContain('aspect={aspect}')
        ->and($source)->toContain("useState<CropRatio>('4:3')")
        ->and($source)->toContain("setRatio('4:3')");
});

it('POST copies crea activo nuevo heredando name/alt sin mutar el origen', function () {
    $source = MediaAsset::factory()->create([
        'name' => 'Banner',
        'alt' => 'alt original',
    ]);
    $originalPath = $source->path;

    $file = UploadedFile::fake()->image('crop.png', 12, 12);

    $this->post(route('admin.gallery.copies', $source), [
        'image' => $file,
    ])->assertRedirect(route('admin.gallery.index'));

    $copy = MediaAsset::query()->whereKeyNot($source->id)->first();

    expect($copy)->not->toBeNull()
        ->and($copy->company_id)->toBeNull()
        ->and($copy->name)->toBe('Banner')
        ->and($copy->alt)->toBe('alt original')
        ->and($copy->path)->not->toBe($originalPath)
        ->and(Storage::disk('public')->exists($copy->path))->toBeTrue();

    $source->refresh();

    expect($source->name)->toBe('Banner')
        ->and($source->alt)->toBe('alt original')
        ->and($source->path)->toBe($originalPath)
        ->and(Storage::disk('public')->exists($originalPath))->toBeTrue();
});

it('POST copies sin create responde 403', function () {
    $source = MediaAsset::factory()->create();

    $actor = User::factory()->create();
    $actor->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $actor->givePermissionTo(['configuracion.access', 'gallery.view', 'gallery.viewAny']);
    actingWithCompany($actor, $this->company);

    $this->post(route('admin.gallery.copies', $source), [
        'image' => UploadedFile::fake()->image('crop.png', 8, 8),
    ])->assertForbidden();

    expect(MediaAsset::query()->count())->toBe(1);
});

it('POST copies con origen fuera de perímetro responde 403', function () {
    $assetB = MediaAsset::factory()->forCompany($this->otherCompany)->create();

    $actorA = User::factory()->create();
    $actorA->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $actorA->givePermissionTo([
        'configuracion.access',
        'gallery.view',
        'gallery.viewAny',
        'gallery.create',
    ]);
    actingWithCompany($actorA, $this->company);

    $this->post(route('admin.gallery.copies', $assetB), [
        'image' => UploadedFile::fake()->image('crop.png', 8, 8),
    ])->assertForbidden();

    expect(MediaAsset::query()->count())->toBe(1);
});

it('POST copies con company_id responde error de validación', function () {
    $source = MediaAsset::factory()->create();

    $this->post(route('admin.gallery.copies', $source), [
        'image' => UploadedFile::fake()->image('crop.png', 8, 8),
        'company_id' => $this->company->id,
    ])->assertSessionHasErrors('company_id');

    expect(MediaAsset::query()->count())->toBe(1);
});

it('picker JSON lista activos paginados con meta', function () {
    $asset = MediaAsset::factory()->create([
        'name' => 'Picker',
        'alt' => 'Alt picker',
    ]);

    $this->getJson(route('admin.gallery.picker', ['page' => 1, 'per_page' => 24]))
        ->assertOk()
        ->assertJsonPath('assets.0.id', $asset->id)
        ->assertJsonPath('assets.0.name', 'Picker')
        ->assertJsonPath('assets.0.alt', 'Alt picker')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 24)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonStructure([
            'assets' => [
                ['id', 'name', 'alt', 'url'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('picker JSON filtra por q en name o alt y pagina', function () {
    MediaAsset::factory()->create(['name' => 'Logo ACME', 'alt' => 'marca']);
    MediaAsset::factory()->create(['name' => 'Banner', 'alt' => 'portada logo']);
    MediaAsset::factory()->create(['name' => 'Otro', 'alt' => 'nada']);

    $response = $this->getJson(route('admin.gallery.picker', [
        'q' => 'logo',
        'page' => 1,
        'per_page' => 24,
    ]))->assertOk();

    $names = collect($response->json('assets'))->pluck('name')->sort()->values()->all();

    expect($names)->toBe(['Banner', 'Logo ACME'])
        ->and($response->json('meta.total'))->toBe(2)
        ->and($response->json('meta.current_page'))->toBe(1);
});

it('picker JSON no devuelve todos cuando hay más de una página', function () {
    MediaAsset::factory()->count(5)->create();

    $response = $this->getJson(route('admin.gallery.picker', [
        'page' => 1,
        'per_page' => 2,
    ]))->assertOk();

    expect($response->json('assets'))->toHaveCount(2)
        ->and($response->json('meta.per_page'))->toBe(2)
        ->and($response->json('meta.total'))->toBe(5)
        ->and($response->json('meta.last_page'))->toBe(3);
});

it('picker JSON responde 403 sin gallery.view', function () {
    actingWithCompany(User::factory()->create(), $this->company);

    $this->getJson(route('admin.gallery.picker'))->assertForbidden();
});

it('picker JSON aisla MediaAsset de company B (R-TEN-05)', function () {
    MediaAsset::factory()->create(['name' => 'Platform']);
    MediaAsset::factory()->forCompany($this->otherCompany)->create(['name' => 'B-only']);

    $actorA = User::factory()->create();
    $actorA->companies()->attach($this->company->id);
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('configuracion.access');
    $actorA->givePermissionTo([
        'configuracion.access',
        'gallery.view',
    ]);
    actingWithCompany($actorA, $this->company);

    $response = $this->getJson(route('admin.gallery.picker'))->assertOk();
    $names = collect($response->json('assets'))->pluck('name');

    expect($names)->toContain('Platform')
        ->and($names)->not->toContain('B-only')
        ->and($response->json('meta'))->toHaveKeys(['current_page', 'last_page', 'per_page', 'total']);
});

it('GalleryInsertDialog usa buscador debounce AbortController e infinite scroll', function () {
    $source = (string) file_get_contents(resource_path('js/Components/Form/GalleryInsertDialog.tsx'));

    expect($source)->toContain('galeria_insertar_buscar')
        ->and($source)->toContain('AbortController')
        ->and($source)->toContain('IntersectionObserver')
        ->and($source)->toContain('gallery-insert-sentinel')
        ->and($source)->toContain("mode === 'replace'")
        ->and($source)->toContain("'append'")
        ->and($source)->toContain('window.setTimeout')
        ->and($source)->toContain(', 300)');
});
