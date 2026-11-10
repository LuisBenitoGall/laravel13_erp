<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\CreateOrLinkCompanyUser;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->usuarios->id,
        'name' => 'Company users',
        'slug' => 'company-users',
        'label' => ['es' => 'Usuarios empresa'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'usuarios');
    enableModuleForCompany($this->other, 'usuarios');

    app(EnsureCompanyDefaultRoles::class)->handle($this->company);
    MenuCatalogCache::forget();
});

function actingCreator(Company $company, bool $withCreate = true): User
{
    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    // Permisos directos (sin rol manager): el sync Basic del manager incluiría
    // company-users.create y enmascararía el 403 del escenario sin create.
    setPermissionsTeamId($company->id);

    $perms = [
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-users.view'),
        Permission::findOrCreate('company-users.viewAny'),
        Permission::findOrCreate('company-users.update'),
        Permission::findOrCreate('company-users.delete'),
    ];

    if ($withCreate) {
        $perms[] = Permission::findOrCreate('company-users.create');
    }

    $user->givePermissionTo($perms);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('expone rutas create/store/edit y CTA Index ya no es máscara', function () {
    expect(Route::has('admin.company-users.create'))->toBeTrue()
        ->and(Route::has('admin.company-users.store'))->toBeTrue()
        ->and(Route::has('admin.company-users.edit'))->toBeTrue();

    $source = file_get_contents(resource_path('js/Pages/Admin/CompanyUsers/Index.tsx'));
    expect($source)->toContain('/admin/company-users/create')
        ->and($source)->toContain('/admin/company-users/${row.id}/edit')
        ->and($source)->not->toContain('alta_usuario_mascara')
        ->and($source)->not->toContain('edicion_usuario_mascara');
});

it('sin company-users.create responde 403 en create/store', function () {
    actingCreator($this->company, withCreate: false);

    $this->get(route('admin.company-users.create'))->assertForbidden();
    $this->post(route('admin.company-users.store'), [
        'name' => 'Nuevo',
        'surname' => 'User',
        'email' => 'nuevo@example.test',
        'status' => true,
        'is_public' => true,
        'send_credentials' => true,
        'role' => Role::MANAGER,
    ])->assertForbidden();
});

it('Create no incluye Super Admin si el actor no es SA', function () {
    actingCreator($this->company);

    $this->get(route('admin.company-users.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CompanyUsers/Create')
            ->where('roleOptions', fn ($options) => collect($options)->pluck('value')->doesntContain(Role::SUPER_ADMIN)));
});

it('alta nueva con credenciales genera password y redirige a Edit stub', function () {
    actingCreator($this->company);

    $response = $this->post(route('admin.company-users.store'), [
        'name' => 'Ana',
        'surname' => 'Pérez',
        'email' => 'ana@example.test',
        'status' => true,
        'is_public' => true,
        'send_credentials' => true,
        'role' => Role::MANAGER,
    ]);

    $user = User::query()->where('email', 'ana@example.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->is_admin)->toBeTrue()
        ->and($user->is_public)->toBeTrue()
        ->and($user->password)->not->toBeNull()
        ->and($user->companies()->where('companies.id', $this->company->id)->exists())->toBeTrue();

    $response->assertRedirect(route('admin.company-users.edit', $user))
        ->assertSessionHas('success', __('credenciales_usuario_mascara'));

    $this->get(route('admin.company-users.edit', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->where('user.email', 'ana@example.test')
            ->where('context', 'company'));
});

it('alta nueva sin credenciales deja password null', function () {
    actingCreator($this->company);

    $this->post(route('admin.company-users.store'), [
        'name' => 'Sin',
        'surname' => 'Pass',
        'email' => 'sinpass@example.test',
        'status' => true,
        'is_public' => false,
        'send_credentials' => false,
        'role' => Role::MANAGER,
    ])->assertRedirect();

    $user = User::query()->where('email', 'sinpass@example.test')->firstOrFail();
    expect($user->getAttributes()['password'])->toBeNull()
        ->and($user->is_public)->toBeFalse();
});

it('rechaza email ya vinculado a la company de sesión', function () {
    $actor = actingCreator($this->company);
    $member = User::factory()->create(['email' => 'ya@example.test']);
    $this->company->users()->attach($member->id);

    $this->actingAs($actor)
        ->post(route('admin.company-users.store'), [
            'name' => 'Otro',
            'email' => 'ya@example.test',
            'status' => true,
            'is_public' => true,
            'send_credentials' => false,
            'role' => Role::MANAGER,
        ])
        ->assertInvalid(['email']);
});

it('rechaza email soft-deleted sin restaurar', function () {
    actingCreator($this->company);
    $trashed = User::factory()->create(['email' => 'gone@example.test']);
    $trashed->delete();

    $this->post(route('admin.company-users.store'), [
        'name' => 'Gone',
        'email' => 'gone@example.test',
        'status' => true,
        'is_public' => true,
        'send_credentials' => false,
        'role' => Role::MANAGER,
    ])->assertInvalid(['email']);

    expect(User::withTrashed()->where('email', 'gone@example.test')->first()?->trashed())->toBeTrue()
        ->and($trashed->fresh()->companies()->where('companies.id', $this->company->id)->exists())->toBeFalse();
});

it('vincula usuario de otra company y rellena huecos', function () {
    actingCreator($this->company);

    $existing = User::factory()->create([
        'name' => 'KeepName',
        'surname' => null,
        'email' => 'otherco@example.test',
        'is_admin' => false,
        'password' => null,
        'is_public' => false,
        'status' => false,
    ]);
    $this->other->users()->attach($existing->id);

    $response = $this->post(route('admin.company-users.store'), [
        'name' => 'Linked',
        'surname' => 'Name',
        'email' => 'otherco@example.test',
        'status' => true,
        'is_public' => true,
        'send_credentials' => true,
        'role' => Role::MANAGER,
    ]);

    $existing->refresh();
    expect($existing->is_admin)->toBeTrue()
        ->and($existing->name)->toBe('KeepName')
        ->and($existing->surname)->toBe('Name')
        ->and($existing->is_public)->toBeFalse()
        ->and($existing->companies()->where('companies.id', $this->company->id)->exists())->toBeTrue()
        ->and($existing->getAttributes()['password'])->not->toBeNull();

    $response->assertRedirect(route('admin.company-users.edit', $existing))
        ->assertSessionHas('success', __('vinculacion_y_credenciales_usuario_mascara'));
});

it('aislamiento R-TEN-05: no edita ni muta miembro solo de otra company', function () {
    actingCreator($this->company);

    $foreign = User::factory()->create(['email' => 'foreign@example.test']);
    $this->other->users()->attach($foreign->id);

    $this->get(route('admin.company-users.edit', $foreign))->assertForbidden();
    $this->patchJson(route('admin.company-users.status', $foreign), ['status' => false])->assertForbidden();
    $this->delete(route('admin.company-users.destroy', $foreign))->assertForbidden();

    expect($foreign->fresh()->companies()->where('companies.id', $this->company->id)->exists())->toBeFalse();
});

it('Action CreateOrLink rechaza company distinta de CurrentCompany', function () {
    $actor = actingCreator($this->company);

    expect(fn () => app(CreateOrLinkCompanyUser::class)->handle($this->other, [
        'name' => 'X',
        'email' => 'perimeter@example.test',
        'status' => true,
        'is_public' => true,
        'send_credentials' => false,
        'role' => Role::MANAGER,
    ], $actor))->toThrow(HttpException::class);

    expect(User::query()->where('email', 'perimeter@example.test')->exists())->toBeFalse();
});

it('SA puede ver Super Admin en selector y asignarlo', function () {
    $sa = User::factory()->create(['status' => true, 'is_admin' => true]);
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');
    $sa->unsetRelation('permissions');

    $this->get(route('admin.company-users.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('roleOptions', fn ($options) => collect($options)->pluck('value')->contains(Role::SUPER_ADMIN)));

    $this->post(route('admin.company-users.store'), [
        'name' => 'Peer',
        'email' => 'peer-sa@example.test',
        'status' => true,
        'is_public' => true,
        'send_credentials' => false,
        'role' => Role::SUPER_ADMIN,
    ])->assertRedirect();

    $peer = User::query()->where('email', 'peer-sa@example.test')->firstOrFail();
    setPermissionsTeamId(null);
    $peer->unsetRelation('roles');
    expect($peer->hasRole(Role::SUPER_ADMIN))->toBeTrue()
        ->and($peer->is_admin)->toBeTrue();
});

it('all-users table solo lista perfiles públicos', function () {
    $actor = actingCreator($this->company);
    setPermissionsTeamId($this->company->id);
    $actor->givePermissionTo(Permission::findOrCreate('all-users.view'));
    $actor->unsetRelation('permissions');

    $public = User::factory()->create(['email' => 'public@example.test', 'is_public' => true]);
    $private = User::factory()->create(['email' => 'private@example.test', 'is_public' => false]);

    $this->get(route('admin.all-users.table'))
        ->assertOk()
        ->assertJsonFragment(['email' => $public->email])
        ->assertJsonMissing(['email' => $private->email]);
});

it('validación exige email y rol del catálogo', function () {
    actingCreator($this->company);

    $this->post(route('admin.company-users.store'), [
        'name' => 'X',
        'email' => '',
        'status' => true,
        'is_public' => true,
        'send_credentials' => false,
        'role' => 'rol-inventado',
    ])->assertInvalid(['email', 'role']);
});

it('password de usuario existente is_admin no se toca al vincular', function () {
    actingCreator($this->company);

    $existing = User::factory()->create([
        'email' => 'adminish@example.test',
        'is_admin' => true,
        'password' => Hash::make('keep-me'),
    ]);
    $hash = $existing->getAttributes()['password'];
    $this->other->users()->attach($existing->id);

    $this->post(route('admin.company-users.store'), [
        'name' => 'Ignored',
        'email' => 'adminish@example.test',
        'status' => true,
        'is_public' => true,
        'send_credentials' => true,
        'role' => Role::MANAGER,
    ])->assertRedirect()
        ->assertSessionHas('success', __('vinculacion_usuario_mascara'));

    expect($existing->fresh()->getAttributes()['password'])->toBe($hash)
        ->and($existing->fresh()->name)->not->toBe('Ignored');
});
