<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Enums\WorkplaceStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Town;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
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
function actingWorkplaceUser(Company $company, array $extra = []): User
{
    if (! Module::query()->where('slug', 'empresas')->exists()) {
        Module::factory()->create([
            'slug' => 'empresas',
            'level' => ModuleLevel::Basic,
            'status' => ModuleStatus::Active,
        ]);
    }
    enableModuleForCompany($company, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $names = array_merge(
        [
            'empresas.access',
            'workplaces.view',
            'workplaces.viewAny',
            'workplaces.create',
            'workplaces.update',
            'workplaces.delete',
        ],
        $extra,
    );
    $user->givePermissionTo(
        collect($names)->map(fn (string $name) => Permission::findOrCreate($name))->all(),
    );
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('menu Empresas incluye Centros de trabajo', function () {
    $this->seed(AdminCatalogSeeder::class);

    $company = Company::query()->where('slug', 'acme')->firstOrFail();
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($user, $company);

    $menu = app(MenuBuilder::class)->forUser($user);
    $empresas = collect($menu)->firstWhere('slug', 'empresas');
    $slugs = collect($empresas['functionalities'] ?? [])->pluck('slug')->all();
    $item = collect($empresas['functionalities'] ?? [])->firstWhere('slug', 'workplaces');

    expect($slugs)->toContain('companies', 'company-directory', 'workplaces')
        ->and($item['href'])->toBe(route('admin.workplaces.index', absolute: false))
        ->and($item['label'])->toBe(__('centros_de_trabajo'));
});

it('CRUD workplaces de la company en sesion con featured unico', function () {
    $company = Company::factory()->create();
    actingWorkplaceUser($company);
    $town = Town::factory()->create();

    $this->post(route('admin.workplaces.store'), [
        'name' => 'Sede Central',
        'address' => 'Calle 1',
        'cp' => '28001',
        'town_id' => $town->id,
        'featured' => false,
        'status' => WorkplaceStatus::Active->value,
    ])->assertRedirect();

    $first = Workplace::query()->where('name', 'Sede Central')->firstOrFail();
    expect($first->featured)->toBeTrue()
        ->and($first->company_id)->toBe($company->id)
        ->and($first->town_id)->toBe($town->id);

    $this->post(route('admin.workplaces.store'), [
        'name' => 'Almacen Norte',
        'address' => null,
        'cp' => null,
        'town_id' => null,
        'featured' => true,
        'status' => WorkplaceStatus::Active->value,
    ])->assertRedirect();

    $second = Workplace::query()->where('name', 'Almacen Norte')->firstOrFail();
    expect($second->featured)->toBeTrue()
        ->and($first->fresh()->featured)->toBeFalse();

    $this->put(route('admin.workplaces.update', $first), [
        'name' => 'Sede Central',
        'address' => 'Calle 1',
        'cp' => '28001',
        'town_id' => $town->id,
        'featured' => true,
        'status' => WorkplaceStatus::Inactive->value,
    ])->assertRedirect();

    expect($first->fresh()->featured)->toBeTrue()
        ->and($second->fresh()->featured)->toBeFalse()
        ->and($first->fresh()->status)->toBe(WorkplaceStatus::Inactive);

    $this->patchJson(route('admin.workplaces.status', $first), [
        'status' => WorkplaceStatus::Active->value,
    ])->assertOk()
        ->assertJson(['status' => 'active']);

    $this->get(route('admin.workplaces.table'))
        ->assertOk()
        ->assertJsonPath('data.0.name', fn ($name) => is_string($name));

    $this->delete(route('admin.workplaces.destroy', $second))->assertRedirect(
        route('admin.workplaces.index'),
    );
    expect(Workplace::query()->whereKey($second->id)->exists())->toBeFalse()
        ->and(Workplace::withTrashed()->whereKey($second->id)->exists())->toBeTrue();
});

it('Create default_featured true sin Active y false si ya hay Active', function () {
    $company = Company::factory()->create();
    actingWorkplaceUser($company);

    $this->get(route('admin.workplaces.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Workplaces/Create')
            ->where('default_featured', true));

    Workplace::factory()->create([
        'company_id' => $company->id,
        'status' => WorkplaceStatus::Active,
        'featured' => true,
    ]);

    $this->get(route('admin.workplaces.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('default_featured', false));
});

it('valida name required y cp max', function () {
    $company = Company::factory()->create();
    actingWorkplaceUser($company);

    $this->from(route('admin.workplaces.create'))
        ->post(route('admin.workplaces.store'), [
            'name' => '',
            'cp' => str_repeat('1', 17),
            'status' => WorkplaceStatus::Active->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors(['name', 'cp']);
});

it('sin workplaces.view responde 403', function () {
    $company = Company::factory()->create();
    if (! Module::query()->where('slug', 'empresas')->exists()) {
        Module::factory()->create([
            'slug' => 'empresas',
            'level' => ModuleLevel::Basic,
            'status' => ModuleStatus::Active,
        ]);
    }
    enableModuleForCompany($company, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);
    setPermissionsTeamId($company->id);
    $user->givePermissionTo(Permission::findOrCreate('empresas.access'));
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $this->get(route('admin.workplaces.index'))->assertForbidden();
});

it('aisla workplaces entre companies (R-TEN-05)', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    withCurrentCompany($companyA);
    $workplaceA = Workplace::factory()->create(['company_id' => $companyA->id, 'name' => 'A HQ']);

    withCurrentCompany($companyB);
    $workplaceB = Workplace::factory()->create(['company_id' => $companyB->id, 'name' => 'B HQ']);

    withCurrentCompany($companyA);
    expect(Workplace::query()->pluck('id')->all())
        ->toContain($workplaceA->id)
        ->not->toContain($workplaceB->id);

    actingWorkplaceUser($companyA);

    $this->get(route('admin.workplaces.edit', $workplaceB))->assertNotFound();
    $this->put(route('admin.workplaces.update', $workplaceB), [
        'name' => 'Hacked',
        'status' => WorkplaceStatus::Active->value,
        'featured' => false,
    ])->assertNotFound();
    $this->delete(route('admin.workplaces.destroy', $workplaceB))->assertNotFound();

    expect(app(CurrentCompany::class)->id())->toBe($companyA->id)
        ->and($workplaceB->fresh()->name)->toBe('B HQ');
});

it('UI Create Edit no incluyen campos NonGoal v1', function () {
    $create = file_get_contents(resource_path('js/Pages/Admin/Workplaces/Create.tsx'));
    $edit = file_get_contents(resource_path('js/Pages/Admin/Workplaces/Edit.tsx'));
    $form = file_get_contents(resource_path('js/Pages/Admin/Workplaces/components/WorkplaceForm.tsx'));
    $index = file_get_contents(resource_path('js/Pages/Admin/Workplaces/Index.tsx'));

    expect($index)->toContain('ServerTable')
        ->and($create)->toContain('WorkplaceForm')
        ->and($create)->toContain('backHref="/admin/workplaces"')
        ->and($edit)->toContain('WorkplaceForm')
        ->and($edit)->toContain('RecordSummary')
        ->and($edit)->toContain('edicion_centro_trabajo')
        ->and($edit)->toContain('backHref="/admin/workplaces"')
        ->and($form)->toContain('FormActions')
        ->and($form)->toContain("t('principal')")
        ->and($form)->toContain("t('direccion')")
        ->and($form)->toContain('md:grid-cols-4')
        ->and($form)->toContain('md:col-span-3')
        ->and($form)->not->toContain('logo')
        ->and($form)->not->toContain('website')
        ->and($form)->not->toContain('nif')
        ->and($form)->not->toContain('zone')
        ->and($form)->not->toContain('email')
        ->and($form)->not->toContain('phone')
        ->and($form)->not->toContain('description');
});
