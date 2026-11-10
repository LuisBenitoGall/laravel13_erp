<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\SyncManagerPermissionsForCompany;
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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    MenuCatalogCache::forget();

    Role::findOrCreate(Role::SUPER_ADMIN);
    Role::findOrCreate(Role::MANAGER);

    $this->company = Company::factory()->create(['status' => CompanyStatus::Active]);
    $this->other = Company::factory()->create(['status' => CompanyStatus::Active]);

    $this->usuarios = app(CreateModule::class)->handle([
        'name' => 'Usuarios',
        'slug' => 'usuarios',
        'label' => ['es' => 'Usuarios'],
        'color' => '#0369a1',
        'icon' => 'users',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    foreach ([
        ['Company users', 'company-users', ['es' => 'Usuarios empresa']],
        ['All users', 'all-users', ['es' => 'Todos los usuarios']],
        ['Company roles', 'company-roles', ['es' => 'Roles de empresa']],
    ] as [$name, $slug, $label]) {
        app(CreateFunctionality::class)->handle([
            'module_id' => $this->usuarios->id,
            'name' => $name,
            'slug' => $slug,
            'label' => $label,
            'status' => FunctionalityStatus::Active->value,
        ]);
    }

    enableModuleForCompany($this->company, 'usuarios');
    enableModuleForCompany($this->other, 'usuarios');
    MenuCatalogCache::forget();
});

function actingManagerWithUsersPerms(Company $company): User
{
    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $user->assignRole(Role::MANAGER);
    $user->givePermissionTo([
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-users.view'),
        Permission::findOrCreate('company-users.update'),
        Permission::findOrCreate('company-users.delete'),
        Permission::findOrCreate('all-users.view'),
    ]);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('menú Usuarios expone Usuarios empresa y Todos los usuarios', function () {
    $user = actingManagerWithUsersPerms($this->company);

    $menu = app(MenuBuilder::class)->forUser($user);
    $usuarios = collect($menu)->firstWhere('slug', 'usuarios');
    $slugs = collect($usuarios['functionalities'] ?? [])->pluck('slug')->all();
    $companyUsers = collect($usuarios['functionalities'] ?? [])->firstWhere('slug', 'company-users');
    $allUsers = collect($usuarios['functionalities'] ?? [])->firstWhere('slug', 'all-users');

    expect($slugs)->toContain('company-users', 'all-users')
        ->and($companyUsers['href'])->toBe(route('admin.company-users.index', absolute: false))
        ->and($companyUsers['label'])->toBe(__('usuarios_empresa'))
        ->and($allUsers['href'])->toBe(route('admin.all-users.index', absolute: false))
        ->and($allUsers['label'])->toBe(__('todos_los_usuarios'));
});

it('Index Todos lista users públicos y solo máscara de notificación en UI', function () {
    $actor = actingManagerWithUsersPerms($this->company);
    $global = User::factory()->create([
        'name' => 'GlobalOne',
        'email' => 'global@example.test',
        'is_public' => true,
    ]);
    $hidden = User::factory()->create([
        'name' => 'HiddenOne',
        'email' => 'hidden@example.test',
        'is_public' => false,
    ]);

    $this->get(route('admin.all-users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/AllUsers/Index'));

    $this->get(route('admin.all-users.table'))
        ->assertOk()
        ->assertJsonFragment(['email' => $global->email])
        ->assertJsonFragment(['email' => $actor->email])
        ->assertJsonMissing(['email' => $hidden->email]);

    $source = file_get_contents(resource_path('js/Pages/Admin/AllUsers/Index.tsx'));
    expect($source)->toContain('enviar_notificacion')
        ->and($source)->toContain('notificacion_mascara')
        ->and($source)->not->toContain('company-users')
        ->and($source)->not->toContain('desactivar')
        ->and($source)->not->toContain('eliminar');
});

it('Usuarios empresa filtra por company de sesión y aísla tenants', function () {
    $actor = actingManagerWithUsersPerms($this->company);

    $inA = User::factory()->create(['email' => 'in-a@example.test']);
    $inA->companies()->attach($this->company->id);

    $onlyB = User::factory()->create(['email' => 'only-b@example.test']);
    $onlyB->companies()->attach($this->other->id);

    $this->get(route('admin.company-users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/CompanyUsers/Index'));

    $emails = collect($this->get(route('admin.company-users.table'))->assertOk()->json('data'))
        ->pluck('email')
        ->all();

    expect($emails)->toContain('in-a@example.test', $actor->email)
        ->and($emails)->not->toContain('only-b@example.test');
});

it('toggle status y detach de Usuarios empresa no borran el User global', function () {
    actingManagerWithUsersPerms($this->company);

    $member = User::factory()->create(['status' => false, 'email' => 'member@example.test']);
    $member->companies()->attach($this->company->id);

    $this->patchJson(route('admin.company-users.status', $member), ['status' => true])
        ->assertOk()
        ->assertJsonPath('status', true);

    expect($member->fresh()->status)->toBeTrue();

    $this->delete(route('admin.company-users.destroy', $member))
        ->assertRedirect();

    expect(User::query()->whereKey($member->id)->exists())->toBeTrue()
        ->and($member->fresh()->companies()->where('companies.id', $this->company->id)->exists())->toBeFalse();
});

it('Create y Edit están cableados sin máscaras de Index', function () {
    actingManagerWithUsersPerms($this->company);

    $source = file_get_contents(resource_path('js/Pages/Admin/CompanyUsers/Index.tsx'));
    expect($source)->toContain('/admin/company-users/create')
        ->and($source)->toContain('/admin/company-users/${row.id}/edit')
        ->and($source)->not->toContain('alta_usuario_mascara')
        ->and($source)->not->toContain('edicion_usuario_mascara')
        ->and($source)->not->toContain("router.put('/admin/company-users");

    expect(Route::has('admin.company-users.store'))->toBeTrue()
        ->and(Route::has('admin.company-users.create'))->toBeTrue()
        ->and(Route::has('admin.company-users.edit'))->toBeTrue()
        ->and(Route::has('admin.company-users.update'))->toBeTrue()
        ->and(Route::has('admin.company-users.password'))->toBeTrue();
});

it('manager recibe all-users.view tras sync Basic', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    setPermissionsTeamId($this->company->id);
    $user->assignRole(Role::MANAGER);
    setPermissionsTeamId(null);

    app(SyncManagerPermissionsForCompany::class)->handle($this->company);

    setPermissionsTeamId($this->company->id);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    expect($user->can('all-users.view'))->toBeTrue()
        ->and($user->can('all-users.viewAny'))->toBeTrue()
        ->and($user->can('usuarios.access'))->toBeTrue();
});

it('sin permiso view responde 403 en Indexes y table', function () {
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    setPermissionsTeamId($this->company->id);
    $user->givePermissionTo(Permission::findOrCreate('usuarios.access'));
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    $this->get(route('admin.all-users.index'))->assertForbidden();
    $this->get(route('admin.all-users.table'))->assertForbidden();
    $this->get(route('admin.company-users.index'))->assertForbidden();
    $this->get(route('admin.company-users.table'))->assertForbidden();
});
