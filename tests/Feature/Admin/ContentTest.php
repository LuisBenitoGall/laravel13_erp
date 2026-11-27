<?php

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Content;
use App\Domains\Admin\Models\MediaAsset;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Services\HelpContentMap;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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

it('Create Help con code explícito persiste exacto y redirige a Edit', function () {
    $response = $this->post(route('admin.contents.store'), [
        'name' => 'Nombre de empresa',
        'kind' => 'help',
        'code' => 'Companies.Name',
    ]);

    $content = Content::query()->firstOrFail();

    $response->assertRedirect(route('admin.contents.edit', $content));

    expect($content->company_id)->toBeNull()
        ->and($content->kind)->toBe(ContentKind::Help)
        ->and($content->status)->toBe(ContentStatus::Inactive)
        ->and($content->code)->toBe('companies.name');

    $this->get(route('admin.contents.edit', $content))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contents/Edit')
            ->where('content.code', 'companies.name')
            ->where('content.kind', 'help')
            ->where('content.status', 'inactive')
            ->has('content.titles')
            ->has('content.created_at')
            ->has('content.updated_at'));
});

it('Create Help sin code responde 422', function () {
    $this->post(route('admin.contents.store'), [
        'name' => 'Sin código',
        'kind' => 'help',
    ])->assertSessionHasErrors('code');

    expect(Content::query()->count())->toBe(0);
});

it('Create Help con code duplicado o formato inválido responde 422', function () {
    Content::factory()->create(['code' => 'companies.nif']);

    $this->post(route('admin.contents.store'), [
        'name' => 'Dup',
        'kind' => 'help',
        'code' => 'companies.nif',
    ])->assertSessionHasErrors('code');

    $this->post(route('admin.contents.store'), [
        'name' => 'Inválido',
        'kind' => 'help',
        'code' => 'Companies Name',
    ])->assertSessionHasErrors('code');

    expect(Content::query()->where('name', 'Dup')->exists())->toBeFalse()
        ->and(Content::query()->where('name', 'Inválido')->exists())->toBeFalse();
});

it('Create Help admite underscore en scope y field del code', function () {
    $this->post(route('admin.contents.store'), [
        'name' => 'Perfil público',
        'kind' => 'help',
        'code' => 'users.is_public',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->post(route('admin.contents.store'), [
        'name' => 'Scope con guion bajo',
        'kind' => 'help',
        'code' => 'user_profile.is_public',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Content::query()->where('code', 'users.is_public')->exists())->toBeTrue()
        ->and(Content::query()->where('code', 'user_profile.is_public')->exists())->toBeTrue();
});

it('Create Help con code soft-deleted responde 422', function () {
    $trashed = Content::factory()->create(['code' => 'companies.tradename']);
    $trashed->delete();

    $this->post(route('admin.contents.store'), [
        'name' => 'Reusar trashed',
        'kind' => 'help',
        'code' => 'companies.tradename',
    ])->assertSessionHasErrors('code');

    expect(Content::query()->where('name', 'Reusar trashed')->exists())->toBeFalse()
        ->and(Content::withTrashed()->where('code', 'companies.tradename')->count())->toBe(1);
});

it('Create Article sin code genera slug desde el nombre', function () {
    $this->post(route('admin.contents.store'), [
        'name' => 'Guía editorial',
        'kind' => 'article',
    ])->assertRedirect();

    $content = Content::query()->firstOrFail();

    expect($content->kind)->toBe(ContentKind::Article)
        ->and($content->status)->toBe(ContentStatus::Inactive)
        ->and($content->code)->toBe('guia-editorial');
});

it('Create UI: code solo si kind help; sin status', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Contents/Create.tsx'));

    expect($source)->toContain('kind')
        ->and($source)->toContain('continuar')
        ->and($source)->toContain('RadioGroup')
        ->and($source)->toContain("kind === 'help'")
        ->and($source)->toContain('contenido_code_hint')
        ->and($source)->not->toContain('Select')
        ->and($source)->not->toContain('status')
        ->and($source)->not->toContain('Wysiwyg')
        ->and($source)->not->toContain('LangTabs');
});

it('Edit real con RecordSummary y form por kind', function () {
    $edit = (string) file_get_contents(resource_path('js/Pages/Admin/Contents/Edit.tsx'));
    $form = (string) file_get_contents(resource_path('js/Pages/Admin/Contents/components/ContentForm.tsx'));

    expect($edit)->toContain('RecordSummary')
        ->and($edit)->toContain('edicion_contenido')
        ->and($edit)->toContain('identifier={content.code}')
        ->and($edit)->toContain("type: 'delete'")
        ->and($edit)->toContain('contenido_confirmar_eliminar')
        ->and($edit)->not->toContain('contenido_edicion_proximamente')
        ->and($form)->toContain('LangTabs')
        ->and($form)->toContain('Wysiwyg')
        ->and($form)->toContain('enableGalleryInsert')
        ->and($form)->toContain("kind === 'article'");
});

it('Wysiwyg inserta galería solo con enableGalleryInsert; Article lo activa', function () {
    $wysiwyg = (string) file_get_contents(resource_path('js/Components/Form/Wysiwyg.tsx'));
    $form = (string) file_get_contents(resource_path('js/Pages/Admin/Contents/components/ContentForm.tsx'));

    expect($wysiwyg)->toContain('enableGalleryInsert')
        ->and($wysiwyg)->toContain('MediaPicker')
        ->and($wysiwyg)->not->toContain('GalleryInsertDialog')
        ->and($wysiwyg)->toContain('setImage')
        ->and($wysiwyg)->toContain('wysiwyg-gallery-insert')
        ->and($form)->toContain('enableGalleryInsert')
        ->and($form)->toContain('{isArticle ? (');
});

it('Update Help persiste textos e invalida HelpContentMap', function () {
    $content = Content::factory()->create([
        'code' => 'edit-tip',
        'kind' => ContentKind::Help,
        'status' => ContentStatus::Inactive,
    ]);

    $map = app(HelpContentMap::class);
    expect($map->forLocale('es'))->not->toHaveKey('edit-tip');

    $this->put(route('admin.contents.update', $content), [
        'name' => 'Tip editado',
        'status' => 'active',
        'title' => ['es' => 'Título ES', 'en' => 'Title EN', 'ca' => 'Títol CA'],
        'excerpt' => ['es' => 'Extracto', 'en' => 'Excerpt', 'ca' => 'Extracte'],
        'referrer' => ['https://example.com/help', 'https://example.com/more'],
        'video' => [],
        'kind' => 'article',
        'code' => 'hacked-code',
    ])->assertRedirect(route('admin.contents.edit', $content));

    $content->refresh();

    expect($content->name)->toBe('Tip editado')
        ->and($content->status)->toBe(ContentStatus::Active)
        ->and($content->kind)->toBe(ContentKind::Help)
        ->and($content->code)->toBe('edit-tip')
        ->and($content->getTranslation('title', 'es'))->toBe('Título ES')
        ->and($content->referrer)->toBe(['https://example.com/help', 'https://example.com/more'])
        ->and($map->forLocale('es'))->toHaveKey('edit-tip')
        ->and($map->forLocale('es')['edit-tip']['title'])->toBe('Título ES')
        ->and($map->forLocale('es')['edit-tip']['referrer'])->toBe([
            'https://example.com/help',
            'https://example.com/more',
        ]);
});

it('Update Article persiste content HTML allowlist', function () {
    $content = Content::factory()->article()->create([
        'code' => 'edit-article',
    ]);

    $this->put(route('admin.contents.update', $content), [
        'name' => 'Artículo',
        'status' => 'inactive',
        'title' => ['es' => 'Artículo ES', 'en' => '', 'ca' => ''],
        'excerpt' => ['es' => 'Resumen', 'en' => '', 'ca' => ''],
        'content' => [
            'es' => '<p>Hola <strong>mundo</strong><script>alert(1)</script></p>',
            'en' => '',
            'ca' => '',
        ],
        'referrer' => [],
        'video' => [
            'https://www.youtube.com/watch?v=abc',
            'https://www.youtube.com/watch?v=def',
        ],
    ])->assertRedirect(route('admin.contents.edit', $content));

    $content->refresh();

    expect($content->getTranslation('content', 'es'))->toContain('<strong>mundo</strong>')
        ->and($content->getTranslation('content', 'es'))->not->toContain('<script>')
        ->and($content->video)->toBe([
            'https://www.youtube.com/watch?v=abc',
            'https://www.youtube.com/watch?v=def',
        ])
        ->and($content->kind)->toBe(ContentKind::Article);
});

it('Update Article acepta video vacío y persiste []', function () {
    $content = Content::factory()->article()->create([
        'code' => 'article-no-video',
        'video' => ['https://www.youtube.com/watch?v=old'],
    ]);

    $this->put(route('admin.contents.update', $content), [
        'name' => 'Sin vídeo',
        'status' => 'inactive',
        'title' => ['es' => 'Título', 'en' => '', 'ca' => ''],
        'excerpt' => ['es' => '', 'en' => '', 'ca' => ''],
        'content' => [
            'es' => '<p>Body</p>',
            'en' => '',
            'ca' => '',
        ],
        'referrer' => [],
        'video' => [],
    ])->assertRedirect(route('admin.contents.edit', $content));

    expect($content->refresh()->video)->toBe([]);
});

it('Update Article persiste img de galería y sobrevive al Edit', function () {
    Storage::fake('local');

    $asset = MediaAsset::factory()->create([
        'name' => 'Banner',
        'alt' => 'Banner alt',
    ]);
    $url = $asset->url();
    $content = Content::factory()->article()->create([
        'code' => 'article-with-img',
    ]);

    $this->put(route('admin.contents.update', $content), [
        'name' => 'Con imagen',
        'status' => 'inactive',
        'title' => ['es' => 'Título', 'en' => '', 'ca' => ''],
        'excerpt' => ['es' => '', 'en' => '', 'ca' => ''],
        'content' => [
            'es' => '<p>Intro <img src="'.$url.'" alt="Banner alt"></p>',
            'en' => '',
            'ca' => '',
        ],
        'referrer' => [],
        'video' => ['https://www.youtube.com/watch?v=img'],
    ])->assertRedirect(route('admin.contents.edit', $content));

    $content->refresh();
    $html = (string) $content->getTranslation('content', 'es');

    expect($html)->toContain('<img src="'.$url.'" alt="Banner alt">');

    $this->get(route('admin.contents.edit', $content))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contents/Edit')
            ->where('content.contents.es', fn ($value) => is_string($value) && str_contains($value, $url)));
});

it('Update Article elimina img con src inseguro', function () {
    $content = Content::factory()->article()->create([
        'code' => 'article-unsafe-img',
    ]);

    $this->put(route('admin.contents.update', $content), [
        'name' => 'Inseguro',
        'status' => 'inactive',
        'title' => ['es' => 'Título', 'en' => '', 'ca' => ''],
        'excerpt' => ['es' => '', 'en' => '', 'ca' => ''],
        'content' => [
            'es' => '<p>X <img src="javascript:alert(1)" alt="x"><img src="data:text/html;base64,xxx" alt="y"></p>',
            'en' => '',
            'ca' => '',
        ],
        'referrer' => [],
        'video' => ['https://www.youtube.com/watch?v=safe'],
    ])->assertRedirect(route('admin.contents.edit', $content));

    $html = (string) $content->refresh()->getTranslation('content', 'es');

    expect($html)->not->toContain('javascript:');
    expect($html)->not->toContain('data:');
    expect($html)->not->toContain('<img');
});

it('Update rechaza referrer no-URL (javascript:)', function () {
    $content = Content::factory()->create([
        'kind' => ContentKind::Help,
    ]);

    $this->from(route('admin.contents.edit', $content))
        ->put(route('admin.contents.update', $content), [
            'name' => 'Tip',
            'status' => 'active',
            'title' => ['es' => 'Título', 'en' => '', 'ca' => ''],
            'excerpt' => ['es' => 'Extracto', 'en' => '', 'ca' => ''],
            'referrer' => ['javascript:alert(1)'],
            'video' => [],
        ])
        ->assertRedirect(route('admin.contents.edit', $content))
        ->assertSessionHasErrors('referrer.0');
});

it('HelpTip renderiza listas referrer y video', function () {
    $source = (string) file_get_contents(resource_path('js/Components/HelpTip.tsx'));

    expect($source)->toContain('tip.referrer')
        ->and($source)->toContain('tip.video')
        ->and($source)->toContain("t('video_url')")
        ->and($source)->toContain("t('leer_mas')");
});

it('Edit form: referrer/video encima de LangTabs y multi-valor', function () {
    $form = (string) file_get_contents(resource_path('js/Pages/Admin/Contents/components/ContentForm.tsx'));
    $referrerPos = strpos($form, 'content-referrer');
    $langTabsPos = strpos($form, '<LangTabs');

    expect($referrerPos)->not->toBeFalse()
        ->and($langTabsPos)->not->toBeFalse()
        ->and($referrerPos)->toBeLessThan($langTabsPos)
        ->and($form)->toContain('StringListField')
        ->and($form)->toContain('anadir_enlace');
});

it('soft delete desde Index', function () {
    $content = Content::factory()->create();

    $this->delete(route('admin.contents.destroy', $content))->assertRedirect();

    expect(Content::query()->whereKey($content->id)->exists())->toBeFalse()
        ->and(Content::withTrashed()->whereKey($content->id)->exists())->toBeTrue();
});

it('toggle status alterna Active/Inactive e invalida HelpContentMap', function () {
    $content = Content::factory()->create([
        'code' => 'toggle-tip',
        'title' => ['es' => 'Tip', 'en' => 'Tip', 'ca' => 'Tip'],
    ]);

    $map = app(HelpContentMap::class);
    expect($map->forLocale('es'))->not->toHaveKey('toggle-tip');

    $this->patchJson(route('admin.contents.status', $content), ['status' => 'active'])
        ->assertOk()
        ->assertJson(['status' => 'active']);

    expect($content->refresh()->status)->toBe(ContentStatus::Active)
        ->and($map->forLocale('es'))->toHaveKey('toggle-tip');

    $this->patchJson(route('admin.contents.status', $content), ['status' => 'inactive'])
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    expect($content->refresh()->status)->toBe(ContentStatus::Inactive)
        ->and($map->forLocale('es'))->not->toHaveKey('toggle-tip');
});

it('Index sin columna Estado y toggle en Acciones', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Contents/Index.tsx'));

    expect($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('/settings/contents/${row.id}/status')
        ->and($source)->not->toContain("field: 'status'");
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
    $this->put(route('admin.contents.update', $content), [
        'name' => 'X',
        'status' => 'active',
        'title' => ['es' => 'X'],
    ])->assertForbidden();
    $this->patchJson(route('admin.contents.status', $content), ['status' => 'active'])
        ->assertForbidden();
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
        ->and($item['href'])->toBe('/settings/contents');
});

it('HelpTip silencia codes inexistentes y no usa JSON runtime', function () {
    $source = (string) file_get_contents(resource_path('js/Components/HelpTip.tsx'));

    expect($source)->toContain('help_tips')
        ->and($source)->toContain('return null')
        ->and(file_exists(storage_path('json/contents.json')))->toBeFalse()
        ->and(Cache::get('contents.help.map.es'))->toBeNull();
});
