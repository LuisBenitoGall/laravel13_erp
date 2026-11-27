<?php

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Sector;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);
});

/**
 * @param  list<string>  $extra
 */
function actingDirectoryViewer(Company $sessionCompany, array $extra = []): User
{
    if (! Module::query()->where('slug', 'empresas')->exists()) {
        Module::factory()->create([
            'slug' => 'empresas',
            'level' => ModuleLevel::Basic,
            'status' => ModuleStatus::Active,
        ]);
    }
    enableModuleForCompany($sessionCompany, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $sessionCompany);

    setPermissionsTeamId($sessionCompany->id);
    $names = array_merge(
        ['companies.access', 'company-directory.view'],
        $extra,
    );
    $user->givePermissionTo(
        collect($names)->map(fn (string $name) => Permission::findOrCreate($name))->all(),
    );
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

function directoryEligibleCompany(string $name, Sector $sector): Company
{
    $company = Company::factory()->create([
        'name' => $name,
        'nif' => fake()->unique()->numerify('B########'),
        'status' => CompanyStatus::Active,
    ]);
    $company->sectors()->attach($sector->id);

    return $company;
}

it('menu Empresas incluye Directorio junto a Mis empresas', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::query()->where('slug', 'acme')->firstOrFail();
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($user, $company);

    $menu = app(MenuBuilder::class)->forUser($user);
    $empresas = collect($menu)->firstWhere('slug', 'empresas');
    $slugs = collect($empresas['functionalities'] ?? [])->pluck('slug')->all();
    $directory = collect($empresas['functionalities'] ?? [])->firstWhere('slug', 'company-directory');

    expect($slugs)->toContain('companies', 'company-directory')
        ->and($slugs)->not->toContain('company-sectors')
        ->and($directory['href'])->toBe(route('admin.company-directory.index', absolute: false))
        ->and($directory['label'])->toBe(__('directorio'));
});

it('universo excluye draft sin NIF o sin sector; incluye elegibles', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingDirectoryViewer($session);

    $sector = Sector::factory()->create(['status' => SectorStatus::Active]);
    $inactiveSector = Sector::factory()->create(['status' => SectorStatus::Inactive]);
    $ok = directoryEligibleCompany('Visible Co', $sector);

    Company::factory()->create([
        'name' => 'Draft Co',
        'nif' => 'B11111111',
        'status' => CompanyStatus::Draft,
    ])->sectors()->attach($sector->id);

    Company::factory()->create([
        'name' => 'No Nif Co',
        'nif' => null,
        'status' => CompanyStatus::Active,
    ])->sectors()->attach($sector->id);

    Company::factory()->create([
        'name' => 'Whitespace Nif Co',
        'nif' => '   ',
        'status' => CompanyStatus::Active,
    ])->sectors()->attach($sector->id);

    Company::factory()->create([
        'name' => 'No Sector Co',
        'nif' => 'B22222222',
        'status' => CompanyStatus::Active,
    ]);

    Company::factory()->create([
        'name' => 'Inactive Sector Co',
        'nif' => 'B44444444',
        'status' => CompanyStatus::Active,
    ])->sectors()->attach($inactiveSector->id);

    $this->get(route('admin.company-directory.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyDirectory/Index')
            ->has('companies.data', 1)
            ->where('companies.data.0.id', $ok->id)
            ->has('sectors')
            ->where('filters.sort', 'name_asc')
            ->missing('companies.table'));

    $source = (string) file_get_contents(resource_path('js/Pages/Admin/CompanyDirectory/Index.tsx'));
    expect($source)->not->toContain('ServerTable')
        ->and($source)->toContain('sector_ids');
});

it('filtro OR por sectores', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingDirectoryViewer($session);

    $a = Sector::factory()->create(['status' => SectorStatus::Active]);
    $b = Sector::factory()->create(['status' => SectorStatus::Active]);
    $c = Sector::factory()->create(['status' => SectorStatus::Active]);

    $withA = directoryEligibleCompany('Only A', $a);
    $withB = directoryEligibleCompany('Only B', $b);
    directoryEligibleCompany('Only C', $c);

    $this->get(route('admin.company-directory.index', [
        'sector_ids' => [$a->id, $b->id],
    ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('companies.data', 2)
            ->where('filters.sector_ids', [$a->id, $b->id])
            ->where('companies.data', fn ($rows) => collect($rows)->pluck('id')->sort()->values()->all()
                === collect([$withA->id, $withB->id])->sort()->values()->all()));
});

it('Show informativo 404 fuera de universo y sin CTAs en source', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingDirectoryViewer($session);

    $sector = Sector::factory()->create(['status' => SectorStatus::Active]);
    $ok = directoryEligibleCompany('Show Me', $sector);
    $draft = Company::factory()->create([
        'nif' => 'B33333333',
        'status' => CompanyStatus::Draft,
    ]);
    $draft->sectors()->attach($sector->id);

    $this->get(route('admin.company-directory.show', $ok))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/CompanyDirectory/Show')
            ->where('company.id', $ok->id)
            ->has('company.sectors'));

    $this->get(route('admin.company-directory.show', $draft))->assertNotFound();

    $show = (string) file_get_contents(resource_path('js/Pages/Admin/CompanyDirectory/Show.tsx'));
    expect($show)->not->toContain('contactar')
        ->and($show)->not->toContain('cliente')
        ->and($show)->not->toContain('proveedor')
        ->and($show)->not->toContain('CustomerProvider');
});

it('sin company-directory.view responde 403', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active]);
    if (! Module::query()->where('slug', 'empresas')->exists()) {
        Module::factory()->create([
            'slug' => 'empresas',
            'level' => ModuleLevel::Basic,
            'status' => ModuleStatus::Active,
        ]);
    }
    enableModuleForCompany($session, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $session);
    setPermissionsTeamId($session->id);
    $user->givePermissionTo(Permission::findOrCreate('companies.access'));

    $this->get(route('admin.company-directory.index'))->assertForbidden();
});

it('Mis empresas Index sigue siendo ServerTable forUser', function () {
    $this->seed(AdminCatalogSeeder::class);
    $company = Company::query()->where('slug', 'acme')->firstOrFail();
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($user, $company);

    $this->get(route('admin.companies.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Companies/Index'));

    $index = (string) file_get_contents(resource_path('js/Pages/Admin/Companies/Index.tsx'));
    expect($index)->toContain('ServerTable')
        ->and($index)->toContain('/companies/table');
});
