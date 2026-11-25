<?php

use App\Domains\Admin\Actions\CreateCompany;
use App\Domains\Admin\Actions\RevokeModuleAuthorizationForCompany;
use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Account;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\CompanyAccount;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Models\Town;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Services\MenuBuilder;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);

    if (! Account::query()->where('is_default', true)->exists()) {
        Account::factory()->default()->create(['status' => AccountStatus::Active]);
    }

    if (! Module::query()->where('slug', 'empresas')->exists()) {
        Module::factory()->create([
            'slug' => 'empresas',
            'level' => ModuleLevel::Basic,
            'status' => ModuleStatus::Active,
        ]);
    }
});

/**
 * Actor con sesión en `$session` y los permisos `delegations.*` indicados.
 *
 * @param  list<string>  $abilities
 */
function actingDelegationUser(Company $session, array $abilities = ['view', 'viewAny', 'create', 'update', 'delete']): User
{
    enableModuleForCompany($session, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $session);

    setPermissionsTeamId($session->id);
    $names = ['empresas.access'];
    foreach ($abilities as $ability) {
        $names[] = "delegations.{$ability}";
    }
    $user->givePermissionTo(collect($names)->map(fn (string $name) => Permission::findOrCreate($name))->all());
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

/** @return array<string, mixed> */
function delegationPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'name' => 'Delegación Norte SL',
        'tradename' => 'Norte',
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active->value,
        'workplace' => [
            'name' => 'Sede Norte',
            'address' => 'Calle Mayor 1',
            'cp' => '28001',
        ],
    ], $overrides);
}

function companyAccountsCount(Company $company): int
{
    return CompanyAccount::query()
        ->withoutGlobalScope('company')
        ->where('company_id', $company->id)
        ->count();
}

// --- 2.1 Alta -------------------------------------------------------------

it('alta de Delegación: parent_id = matriz, creador vinculado, workplace en matriz + pivote, sin company_accounts', function () {
    $matrix = Company::factory()->create(['slug' => 'matriz']);
    $actor = actingDelegationUser($matrix);

    $this->post(route('admin.delegations.store'), delegationPayload())
        ->assertRedirect();

    $delegation = Company::query()->where('nif', '12345678Z')->firstOrFail();

    expect($delegation->parent_id)->toBe($matrix->id)
        ->and($delegation->status)->toBe(CompanyStatus::Active)
        ->and($actor->companies()->whereKey($delegation->id)->exists())->toBeTrue()
        ->and(companyAccountsCount($delegation))->toBe(0);

    $workplaces = Workplace::query()->withoutGlobalScope('company')
        ->where('company_id', $matrix->id)
        ->where('name', 'Sede Norte')
        ->get();

    expect($workplaces)->toHaveCount(1)
        ->and($workplaces->first()?->featured)->toBeTrue();

    expect($delegation->workplacesViaMap()->pluck('workplaces.id')->all())
        ->toBe([$workplaces->first()->id])
        ->and((bool) $delegation->workplacesViaMap()->first()?->pivot->is_preferred)->toBeTrue();

    // La sesión sigue en la matriz (no se cambia CurrentCompany a la hija).
    expect(app(CurrentCompany::class)->id())->toBe($matrix->id)
        ->and(session('current_company_id'))->toBe($matrix->id);

    // Roles materializados + manager del creador en la delegación.
    setPermissionsTeamId($delegation->id);
    $actor->unsetRelation('roles');
    expect($actor->hasRole('manager'))->toBeTrue();
    setPermissionsTeamId($matrix->id);
});

it('alta de Delegación vinculando workplaces existentes de la matriz', function () {
    $matrix = Company::factory()->create();
    $existing = Workplace::factory()->create(['company_id' => $matrix->id, 'name' => 'Ya existe']);
    actingDelegationUser($matrix);

    $payload = delegationPayload();
    unset($payload['workplace']);
    $payload['workplace_ids'] = [$existing->id];

    $this->post(route('admin.delegations.store'), $payload)->assertRedirect();

    $delegation = Company::query()->where('nif', '12345678Z')->firstOrFail();

    expect(Workplace::query()->withoutGlobalScope('company')->where('company_id', $delegation->id)->count())
        ->toBe(0)
        ->and($delegation->workplacesViaMap()->pluck('workplaces.id')->all())->toBe([$existing->id]);
});

it('ignora un parent_id enviado por el cliente', function () {
    $matrix = Company::factory()->create();
    $other = Company::factory()->create();
    actingDelegationUser($matrix);

    $this->post(route('admin.delegations.store'), delegationPayload(['parent_id' => $other->id]))
        ->assertRedirect();

    expect(Company::query()->where('nif', '12345678Z')->value('parent_id'))->toBe($matrix->id);
});

it('alta de Delegación exige centro nuevo o workplace_ids', function () {
    $matrix = Company::factory()->create();
    actingDelegationUser($matrix);

    $this->post(route('admin.delegations.store'), delegationPayload(['workplace' => ['name' => '']]))
        ->assertSessionHasErrors('workplace.name');

    expect(Company::query()->where('nif', '12345678Z')->exists())->toBeFalse();
});

it('alta de Delegación persiste town_id del centro nuevo en la matriz', function () {
    $matrix = Company::factory()->create();
    actingDelegationUser($matrix);
    $town = Town::factory()->create();

    $this->post(route('admin.delegations.store'), delegationPayload([
        'workplace' => ['town_id' => $town->id],
    ]))->assertRedirect();

    $workplace = Workplace::query()->withoutGlobalScope('company')
        ->where('company_id', $matrix->id)
        ->where('name', 'Sede Norte')
        ->firstOrFail();

    expect($workplace->town_id)->toBe($town->id);
});

it('cascada geo de workplaces es usable con solo permisos delegations', function () {
    $matrix = Company::factory()->create();
    actingDelegationUser($matrix, ['view', 'viewAny', 'create', 'update']);
    $town = Town::factory()->create();
    $province = $town->province;
    $country = $province->country;

    $this->getJson(route('admin.workplaces.geo.countries'))->assertOk();
    $this->getJson(route('admin.workplaces.geo.provinces', $country))->assertOk()
        ->assertJsonFragment(['id' => $province->id]);
    $this->getJson(route('admin.workplaces.geo.towns', $province))->assertOk()
        ->assertJsonFragment(['id' => $town->id]);
});

it('el alta genérica de Mis empresas sigue creando company_accounts Active', function () {
    $creator = User::factory()->create();
    $company = app(CreateCompany::class)->handle([
        'name' => 'Normal Co',
        'slug' => 'normal-co',
        'tradename' => 'Normal',
        'nif' => null,
        'status' => CompanyStatus::Active->value,
        'logo' => null,
    ], $creator);

    expect(companyAccountsCount($company))->toBe(1)
        ->and($company->parent_id)->toBeNull();
});

it('Create de Delegaciones es otra ruta/página que Create de Mis empresas', function () {
    $matrix = Company::factory()->create();
    actingDelegationUser($matrix);

    $this->get(route('admin.delegations.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Delegations/Create')
            ->has('countries'));

    expect(route('admin.delegations.create', absolute: false))
        ->not->toBe(route('admin.companies.create', absolute: false));
});

// --- 2.2 Index / perímetro ------------------------------------------------

it('table lista solo hijas de la matriz de sesión', function () {
    $matrix = Company::factory()->create(['name' => 'Matriz A']);
    $otherMatrix = Company::factory()->create(['name' => 'Matriz B']);
    $mine = Company::factory()->create(['name' => 'Hija de A', 'parent_id' => $matrix->id]);
    $foreign = Company::factory()->create(['name' => 'Hija de B', 'parent_id' => $otherMatrix->id]);
    $standalone = Company::factory()->create(['name' => 'Suelta']);
    actingDelegationUser($matrix);

    $response = $this->getJson(route('admin.delegations.table'))->assertOk();
    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toBe([$mine->id])
        ->and($ids)->not->toContain($foreign->id, $standalone->id, $matrix->id);

    $this->get(route('admin.delegations.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Delegations/Index'));
});

it('responde 403 si la sesión no es matriz (company con parent_id)', function () {
    $matrix = Company::factory()->create();
    $child = Company::factory()->create(['parent_id' => $matrix->id]);
    actingDelegationUser($child);

    $this->get(route('admin.delegations.index'))->assertForbidden();
    $this->getJson(route('admin.delegations.table'))->assertForbidden();
    $this->get(route('admin.delegations.create'))->assertForbidden();
    $this->post(route('admin.delegations.store'), delegationPayload())->assertForbidden();

    expect(Company::query()->where('nif', '12345678Z')->exists())->toBeFalse();
});

it('Super Admin tampoco bypasea el perímetro de sesión matriz', function () {
    $matrix = Company::factory()->create();
    $child = Company::factory()->create(['parent_id' => $matrix->id]);
    $sa = User::factory()->create(['status' => true]);
    $sa->assignRole(Role::SUPER_ADMIN);
    enableModuleForCompany($child, 'empresas');
    actingWithCompany($sa, $child);

    $this->get(route('admin.delegations.index'))->assertForbidden();
    $this->post(route('admin.delegations.store'), delegationPayload())->assertForbidden();
});

// --- 2.3 Update / delete + authz -----------------------------------------

it('actualiza campos de empresa de una Delegación propia sin tocar parent_id', function () {
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create(['parent_id' => $matrix->id, 'name' => 'Vieja']);
    actingDelegationUser($matrix);

    $this->get(route('admin.delegations.edit', $delegation))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Delegations/Edit')
            ->where('delegation.id', $delegation->id)
            ->where('matrix.id', $matrix->id)
            ->has('linked_workplaces', 0)
            ->missing('workplaces')
            ->missing('countries'));

    $this->put(route('admin.delegations.update', $delegation), [
        'name' => 'Nueva',
        'tradename' => 'N',
        'nif' => '87654321X',
        'status' => CompanyStatus::Active->value,
        'parent_id' => null,
    ])->assertRedirect();

    $fresh = $delegation->fresh();
    expect($fresh->name)->toBe('Nueva')
        ->and($fresh->nif)->toBe('87654321X')
        ->and($fresh->parent_id)->toBe($matrix->id);
});

it('soft delete de una Delegación propia', function () {
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create(['parent_id' => $matrix->id]);
    actingDelegationUser($matrix);

    $this->delete(route('admin.delegations.destroy', $delegation))
        ->assertRedirect(route('admin.delegations.index'));

    expect(Company::query()->whereKey($delegation->id)->exists())->toBeFalse()
        ->and(Company::withTrashed()->whereKey($delegation->id)->exists())->toBeTrue();
});

it('toggle de status active↔closed de una Delegación propia', function () {
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create([
        'parent_id' => $matrix->id,
        'status' => CompanyStatus::Active,
    ]);
    actingDelegationUser($matrix);

    $this->patchJson(route('admin.delegations.status', $delegation), [
        'status' => CompanyStatus::Closed->value,
    ])->assertOk()
        ->assertJsonPath('status', 'closed');

    expect($delegation->fresh()->status)->toBe(CompanyStatus::Closed);

    $this->patchJson(route('admin.delegations.status', $delegation), [
        'status' => CompanyStatus::Active->value,
    ])->assertOk()
        ->assertJsonPath('status', 'active');

    expect($delegation->fresh()->status)->toBe(CompanyStatus::Active);
});

it('no opera sobre hijas de otra matriz ni sobre matrices (404)', function () {
    $matrix = Company::factory()->create();
    $otherMatrix = Company::factory()->create();
    $foreign = Company::factory()->create(['parent_id' => $otherMatrix->id]);
    actingDelegationUser($matrix);

    $this->get(route('admin.delegations.edit', $foreign))->assertNotFound();
    $this->put(route('admin.delegations.update', $foreign), [
        'name' => 'X',
        'nif' => '87654321X',
        'status' => CompanyStatus::Active->value,
    ])->assertNotFound();
    $this->delete(route('admin.delegations.destroy', $foreign))->assertNotFound();
    $this->get(route('admin.delegations.edit', $otherMatrix))->assertNotFound();

    expect($foreign->fresh()->name)->toBe($foreign->name);
});

it('authz delegations.*: cada acción exige su permiso', function () {
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create(['parent_id' => $matrix->id]);
    actingDelegationUser($matrix, ['view']);

    $this->get(route('admin.delegations.index'))->assertOk();
    $this->get(route('admin.delegations.create'))->assertForbidden();
    $this->post(route('admin.delegations.store'), delegationPayload())->assertForbidden();
    $this->get(route('admin.delegations.edit', $delegation))->assertForbidden();
    $this->put(route('admin.delegations.update', $delegation), [
        'name' => 'X',
        'nif' => '87654321X',
        'status' => CompanyStatus::Active->value,
    ])->assertForbidden();
    $this->delete(route('admin.delegations.destroy', $delegation))->assertForbidden();
});

it('sin delegations.view el Index responde 403', function () {
    $matrix = Company::factory()->create();
    actingDelegationUser($matrix, []);

    $this->get(route('admin.delegations.index'))->assertForbidden();
    $this->getJson(route('admin.delegations.table'))->assertForbidden();
});

// --- Opción A: sin CRUD de sedes en Delegación ----------------------------

it('rutas antiguas delegations.workplaces.* ya no existen', function () {
    expect(Route::has('admin.delegations.workplaces.store'))->toBeFalse()
        ->and(Route::has('admin.delegations.workplaces.update'))->toBeFalse()
        ->and(Route::has('admin.delegations.workplaces.destroy'))->toBeFalse();
});

// --- 1.1 Catálogo / menú --------------------------------------------------

it('catálogo: Functionality delegations bajo empresas con permisos y aside', function () {
    $this->seed(AdminCatalogSeeder::class);

    $functionality = Functionality::query()->where('slug', 'delegations')->firstOrFail();
    expect($functionality->module->slug)->toBe('empresas');

    foreach (['view', 'viewAny', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "delegations.{$ability}")->exists())->toBeTrue();
    }

    $company = Company::query()->where('slug', 'acme')->firstOrFail();

    // Manager de la company materializada recibe los permisos sincronizados.
    setPermissionsTeamId($company->id);
    $manager = Role::query()->where('name', 'manager')->where('company_id', $company->id)->firstOrFail();
    expect($manager->permissions->pluck('name')->all())->toContain('delegations.view', 'delegations.update');
    setPermissionsTeamId(null);

    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);
    actingWithCompany($user, $company);

    $menu = app(MenuBuilder::class)->forUser($user);
    $empresas = collect($menu)->firstWhere('slug', 'empresas');
    $item = collect($empresas['functionalities'] ?? [])->firstWhere('slug', 'delegations');

    expect($item['href'])->toBe(route('admin.delegations.index', absolute: false))
        ->and($item['label'])->toBe(__('delegaciones'));
});

// --- 3.1 Switcher solo matrices ------------------------------------------

it('available_companies del shell no incluye delegaciones', function () {
    $matrix = Company::factory()->create(['name' => 'Matriz']);
    $child = Company::factory()->create(['name' => 'Hija', 'parent_id' => $matrix->id]);
    Module::factory()->create([
        'slug' => 'configuracion',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);

    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);
    $user->companies()->attach($child->id);
    actingWithCompany($user, $matrix);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('available_companies', 1)
            ->where('available_companies.0.id', $matrix->id));
});

it('companies.switch rechaza una delegación vinculada', function () {
    $matrix = Company::factory()->create();
    $child = Company::factory()->create(['parent_id' => $matrix->id]);
    $user = User::factory()->create(['status' => true]);
    $user->companies()->attach($child->id);
    actingWithCompany($user, $matrix);

    $this->post(route('companies.switch'), ['company_id' => $child->id])
        ->assertSessionHasErrors('company_id');

    expect(session('current_company_id'))->toBe($matrix->id);
});

it('companies.select.store rechaza una delegación vinculada', function () {
    $matrix = Company::factory()->create();
    $child = Company::factory()->create(['parent_id' => $matrix->id]);
    $user = User::factory()->create(['status' => true]);
    $user->companies()->attach([$matrix->id, $child->id]);
    $this->actingAs($user);

    $this->post(route('companies.select.store'), ['company_id' => $child->id])
        ->assertSessionHasErrors('company_id');

    expect(session('current_company_id'))->toBeNull();

    $this->post(route('companies.select.store'), ['company_id' => $matrix->id])
        ->assertRedirect();

    expect(session('current_company_id'))->toBe($matrix->id);
});

it('select y login solo ofrecen matrices; una matriz + hijas entra automático', function () {
    $matrix = Company::factory()->create();
    $child = Company::factory()->create(['parent_id' => $matrix->id]);
    $user = User::factory()->create(['email' => 'grupo@example.com', 'password' => 'password']);
    $user->companies()->attach([$matrix->id, $child->id]);

    $this->post(route('login'), ['email' => 'grupo@example.com', 'password' => 'password'])
        ->assertRedirect();

    expect(session('current_company_id'))->toBe($matrix->id);
});

it('usuario solo vinculado a delegaciones no entra en sesión hija: select vacío', function () {
    $matrix = Company::factory()->create();
    $child = Company::factory()->create(['parent_id' => $matrix->id]);
    $user = User::factory()->create(['email' => 'solo@example.com', 'password' => 'password']);
    $user->companies()->attach($child->id);

    $this->post(route('login'), ['email' => 'solo@example.com', 'password' => 'password'])
        ->assertRedirect(route('companies.select'));

    expect(session('current_company_id'))->toBeNull();

    $this->get(route('companies.select'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Auth/SelectCompany')
            ->has('companies', 0));
});

// --- Sus centros (único listado RO) ---------------------------------------

it('edit expone linked_workplaces con preferida y empty state', function () {
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create(['parent_id' => $matrix->id]);
    $linkedPreferred = Workplace::factory()->create([
        'company_id' => $matrix->id,
        'name' => 'Sede Preferida',
    ]);
    $linkedOther = Workplace::factory()->create([
        'company_id' => $matrix->id,
        'name' => 'Sede Secundaria',
    ]);
    $foreignWorkplace = Workplace::factory()->create([
        'company_id' => Company::factory()->create()->id,
        'name' => 'Ajena',
    ]);

    $linkedPreferred->delegations()->attach($delegation->id, ['is_preferred' => true]);
    $linkedOther->delegations()->attach($delegation->id, ['is_preferred' => false]);
    $foreignWorkplace->delegations()->attach($delegation->id, ['is_preferred' => false]);

    actingDelegationUser($matrix);

    $this->get(route('admin.delegations.edit', $delegation))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Delegations/Edit')
            ->has('linked_workplaces', 2)
            ->where('linked_workplaces.0.id', $linkedPreferred->id)
            ->where('linked_workplaces.0.name', 'Sede Preferida')
            ->where('linked_workplaces.0.is_preferred', true)
            ->where('linked_workplaces.1.id', $linkedOther->id)
            ->where('linked_workplaces.1.is_preferred', false)
            ->missing('workplaces'));

    $orphan = Company::factory()->create(['parent_id' => $matrix->id]);
    $this->get(route('admin.delegations.edit', $orphan))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('linked_workplaces', 0));
});

it('UI Edit Delegación es un solo listado Sus centros sin CRUD hijo', function () {
    $edit = file_get_contents(resource_path('js/Pages/Admin/Delegations/Edit.tsx'));
    $panel = file_get_contents(
        resource_path('js/Pages/Admin/Delegations/components/MatrixWorkplacesLinkedPanel.tsx'),
    );
    $create = file_get_contents(resource_path('js/Pages/Admin/Delegations/Create.tsx'));
    $form = file_get_contents(
        resource_path('js/Pages/Admin/Delegations/components/DelegationForm.tsx'),
    );

    expect($edit)->toContain('MatrixWorkplacesLinkedPanel')
        ->and($edit)->toContain('linked_workplaces')
        ->and($edit)->not->toContain('DelegationWorkplacesPanel')
        ->and($panel)->toContain('data-testid="delegation-linked-workplaces"')
        ->and($panel)->toContain('sus_centros')
        ->and($panel)->not->toContain('useForm')
        ->and($create)->toContain('matrix_workplaces')
        ->and($form)->toContain('usar_centros_existentes')
        ->and($form)->toContain('centro_trabajo_empresa')
        ->and($form)->not->toContain('N:M')
        ->and($form)->not->toContain('mapa');
});

// --- V-21 módulo empresas -------------------------------------------------

dataset('delegation module routes', [
    'index' => ['admin.delegations.index'],
    'table' => ['admin.delegations.table'],
    'create' => ['admin.delegations.create'],
]);

it('V-21 bloquea rutas si empresas no está autorizado para la company', function (string $route) {
    $matrix = Company::factory()->create();
    actingDelegationUser($matrix);

    $module = Module::query()->where('slug', 'empresas')->firstOrFail();
    app(RevokeModuleAuthorizationForCompany::class)->handle($matrix, $module);

    $this->get(route($route))->assertForbidden();
})->with('delegation module routes');

it('V-21 bloquea rutas si el catálogo empresas está Inactive', function (string $route) {
    $matrix = Company::factory()->create();
    actingDelegationUser($matrix);

    Module::query()->where('slug', 'empresas')->update(['status' => ModuleStatus::Inactive]);

    $this->get(route($route))->assertForbidden();
})->with('delegation module routes');

it('V-21 permite acceso con capas satisfechas y permiso', function () {
    $matrix = Company::factory()->create();
    actingDelegationUser($matrix);

    $this->get(route('admin.delegations.index'))->assertOk();
});
