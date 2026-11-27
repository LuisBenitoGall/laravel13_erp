<?php

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Sector;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\SectorsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->seed(AdminCatalogSeeder::class);

    $this->company = Company::factory()->create();
    enableModuleForCompany($this->company, 'configuracion');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

it('Super Admin abre Index de sectores', function () {
    $this->get(route('admin.sectors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Sectors/Index'));
});

it('Index sectores sin columna Estado y con Acciones toggle, Edit y Delete', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Sectors/Index.tsx'));

    expect($source)->not->toContain("field: 'status'")
        ->and($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('icon: Pencil')
        ->and($source)->toContain('LangTabs')
        ->and($source)->toContain('persistKey="admin.sectors"');
});

it('Index GET no crea filas (sin import lateral)', function () {
    expect(Sector::withTrashed()->count())->toBe(0);

    $this->get(route('admin.sectors.index'))->assertOk();
    $this->getJson(route('admin.sectors.table'))->assertOk();

    expect(Sector::withTrashed()->count())->toBe(0);
});

it('sin sectors.* las rutas de sectores responden 403', function () {
    $sector = Sector::factory()->create();

    actingWithCompany(User::factory()->create(), $this->company);

    $this->get(route('admin.sectors.index'))->assertForbidden();
    $this->getJson(route('admin.sectors.table'))->assertForbidden();
    $this->post(route('admin.sectors.store'), [
        'name' => ['es' => 'Nuevo'],
    ])->assertForbidden();
    $this->put(route('admin.sectors.update', $sector), [
        'name' => ['es' => 'X'],
    ])->assertForbidden();
    $this->patch(route('admin.sectors.status', $sector))->assertForbidden();
    $this->delete(route('admin.sectors.destroy', $sector))->assertForbidden();

    expect(Sector::query()->count())->toBe(1);
});

it('usuario con sectors.view abre el Index', function () {
    $user = User::factory()->create();
    setPermissionsTeamId($this->company->id);
    Permission::findOrCreate('settings.access');
    $user->givePermissionTo(['settings.access', 'sectors.view']);
    actingWithCompany($user, $this->company);

    $this->get(route('admin.sectors.index'))->assertOk();
});

it('seeder deja la Functionality sectors con permisos sectors.*', function () {
    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "sectors.{$ability}")->exists())->toBeTrue();
    }
});

it('crea sector con traducciones, slug y status Active', function () {
    $this->post(route('admin.sectors.store'), [
        'name' => [
            'es' => 'Construcción Naval',
            'en' => 'Shipbuilding',
            'ca' => 'Construcció naval',
        ],
    ])->assertRedirect();

    $sector = Sector::query()->where('slug', 'construccion-naval')->firstOrFail();

    expect($sector->status)->toBe(SectorStatus::Active)
        ->and($sector->getTranslation('name', 'es'))->toBe('Construcción Naval')
        ->and($sector->getTranslation('name', 'en'))->toBe('Shipbuilding');

    $this->post(route('admin.sectors.store'), [
        'name' => ['en' => 'Only English'],
    ])->assertSessionHasErrors('name.es');
});

it('update cambia nombres y regenera slug desde locale default', function () {
    $sector = Sector::factory()->create([
        'slug' => 'viejo',
        'name' => ['es' => 'Viejo', 'en' => 'Old', 'ca' => 'Vell'],
    ]);

    $this->put(route('admin.sectors.update', $sector), [
        'name' => [
            'es' => 'Sector Nuevo',
            'en' => 'New Sector',
            'ca' => 'Sector Nou',
        ],
    ])->assertRedirect();

    $sector->refresh();

    expect($sector->getTranslation('name', 'es'))->toBe('Sector Nuevo')
        ->and($sector->slug)->toBe('sector-nuevo');
});

it('toggle status alterna Active/Inactive', function () {
    $sector = Sector::factory()->create();

    $this->patchJson(route('admin.sectors.status', $sector))
        ->assertOk()
        ->assertJson(['status' => 'inactive']);

    $this->patchJson(route('admin.sectors.status', $sector), ['status' => 'active'])
        ->assertOk()
        ->assertJson(['status' => 'active']);

    expect($sector->refresh()->status)->toBe(SectorStatus::Active);
});

it('borra sector con soft delete y detach pivot', function () {
    $sector = Sector::factory()->create();
    $this->company->sectors()->attach($sector->id);

    $this->delete(route('admin.sectors.destroy', $sector))
        ->assertRedirect(route('admin.sectors.index'));

    expect(Sector::query()->whereKey($sector->id)->exists())->toBeFalse()
        ->and(Sector::withTrashed()->whereKey($sector->id)->exists())->toBeTrue()
        ->and($this->company->sectors()->count())->toBe(0);
});

it('table sin sorts aplica Active-first y luego name', function () {
    Sector::factory()->create([
        'name' => ['es' => 'Zzz Sector Active', 'en' => 'Zzz', 'ca' => 'Zzz'],
        'status' => SectorStatus::Active,
    ]);
    Sector::factory()->inactive()->create([
        'name' => ['es' => 'Aaa Sector Inactive', 'en' => 'Aaa', 'ca' => 'Aaa'],
    ]);
    Sector::factory()->create([
        'name' => ['es' => 'Mmm Sector Active', 'en' => 'Mmm', 'ca' => 'Mmm'],
        'status' => SectorStatus::Active,
    ]);

    $rows = collect($this->getJson(route('admin.sectors.table', [
        'size' => 200,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Sector']],
    ]))->assertOk()->json('data'));

    expect($rows->pluck('name')->all())->toBe([
        'Mmm Sector Active',
        'Zzz Sector Active',
        'Aaa Sector Inactive',
    ]);
});

it('sectores es catálogo global sin company_id y visible desde dos companies', function () {
    expect(Schema::hasColumn('sectors', 'company_id'))->toBeFalse();

    Sector::factory()->create([
        'name' => ['es' => 'Atlantis Sector', 'en' => 'Atlantis', 'ca' => 'Atlantis'],
    ]);
    $otherCompany = Company::factory()->create();
    enableModuleForCompany($otherCompany, 'configuracion');

    $names = fn () => collect($this->getJson(route('admin.sectors.table'))->assertOk()->json('data'))->pluck('name');

    expect($names())->toContain('Atlantis Sector');

    actingWithCompany($this->user, $otherCompany);

    expect($names())->toContain('Atlantis Sector');
});

it('SectorsSeeder crea 19 secciones NACE A-S de forma idempotente', function () {
    $naceSlugs = [
        'agricultura-ganaderia-silvicultura-pesca',
        'industrias-extractivas',
        'industria-manufacturera',
        'energia-gas-vapor-aire',
        'agua-saneamiento-residuos',
        'construccion',
        'comercio-reparacion-vehiculos',
        'transporte-almacenamiento',
        'hosteleria',
        'informacion-comunicaciones',
        'finanzas-seguros',
        'inmobiliarias',
        'profesionales-cientificas-tecnicas',
        'administrativas-servicios-auxiliares',
        'administracion-publica-defensa',
        'educacion',
        'sanidad-servicios-sociales',
        'artisticas-recreativas-entretenimiento',
        'otros-servicios',
    ];

    $this->seed(SectorsSeeder::class);

    expect($naceSlugs)->toHaveCount(19);

    foreach ($naceSlugs as $slug) {
        $sector = Sector::query()->where('slug', $slug)->first();
        expect($sector)->not->toBeNull()
            ->and($sector->status)->toBe(SectorStatus::Active)
            ->and($sector->getTranslation('name', 'es'))->not->toBe('')
            ->and($sector->getTranslation('name', 'en'))->not->toBe('')
            ->and($sector->getTranslation('name', 'ca'))->not->toBe('');
    }

    $source = (string) file_get_contents(database_path('seeders/SectorsSeeder.php'));
    expect($source)->not->toContain('hogares')
        ->and($source)->not->toContain('extraterritorial')
        ->and($source)->not->toContain('extraterritoriales');

    $construction = Sector::query()->where('slug', 'construccion')->firstOrFail();
    expect($construction->getTranslation('name', 'es'))->toBe('Construcción')
        ->and($construction->getTranslation('name', 'en'))->toBe('Construction')
        ->and($construction->getTranslation('name', 'ca'))->toBe('Construcció');

    $count = Sector::query()->whereIn('slug', $naceSlugs)->count();
    expect($count)->toBe(19);

    $this->seed(SectorsSeeder::class);
    expect(Sector::query()->whereIn('slug', $naceSlugs)->count())->toBe(19);
});
