<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Actions\EnsureCompanyDefaultRoles;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Domains\Admin\Support\UserProfileAuthorization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
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

function actingEditor(Company $company, bool $withUpdate = true): User
{
    $user = User::factory()->create([
        'status' => true,
        'password' => Hash::make('password'),
    ]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $perms = [
        Permission::findOrCreate('usuarios.access'),
        Permission::findOrCreate('company-users.view'),
        Permission::findOrCreate('company-users.viewAny'),
    ];
    if ($withUpdate) {
        $perms[] = Permission::findOrCreate('company-users.update');
    }
    $user->givePermissionTo($perms);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('profile y company-users.edit renderizan la misma página Users/Edit', function () {
    $actor = actingEditor($this->company);
    $member = User::factory()->create(['name' => 'Member']);
    $this->company->users()->attach($member->id);

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->where('context', 'profile')
            ->where('user.id', $actor->id)
            ->where('can.change_password', true)
            ->has('user.created_at')
            ->missing('perfil_proximamente'));

    $this->get(route('admin.company-users.edit', $member))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->where('context', 'company')
            ->where('user.id', $member->id)
            ->where('can.change_password', false));

    $source = file_get_contents(resource_path('js/Pages/Users/Edit.tsx'));
    expect($source)->not->toContain('perfil_proximamente')
        ->and($source)->not->toContain('edicion_usuario_stub')
        ->and($source)->toContain('UserProfileReadout')
        ->and($source)->toContain('RecordEditLayout');
});

it('update ajeno sin company-users.update responde 403', function () {
    actingEditor($this->company, withUpdate: false);
    $member = User::factory()->create();
    $this->company->users()->attach($member->id);

    $this->get(route('admin.company-users.edit', $member))->assertForbidden();
    $this->put(route('admin.company-users.update', $member), [
        'name' => 'Hack',
        'email' => $member->email,
        'status' => true,
        'is_public' => true,
    ])->assertForbidden();
});

it('update ajeno fuera de perímetro responde 403', function () {
    actingEditor($this->company);
    $foreign = User::factory()->create();
    $this->other->users()->attach($foreign->id);

    $this->get(route('admin.company-users.edit', $foreign))->assertForbidden();
    $this->put(route('admin.company-users.update', $foreign), [
        'name' => 'Hack',
        'email' => $foreign->email,
    ])->assertForbidden();
});

it('self actualiza perfil incluyendo is_public y status', function () {
    $actor = actingEditor($this->company);

    $this->put(route('admin.profile.update'), [
        'name' => 'Ada',
        'surname' => 'Lovelace',
        'email' => 'ada-new@example.test',
        'nif' => '12345678Z',
        'birth_date' => '1815-12-10',
        'status' => false,
        'is_public' => false,
    ])->assertRedirect();

    $actor->refresh();
    expect($actor->name)->toBe('Ada')
        ->and($actor->surname)->toBe('Lovelace')
        ->and($actor->email)->toBe('ada-new@example.test')
        ->and($actor->nif)->toBe('12345678Z')
        ->and($actor->birth_date?->format('Y-m-d'))->toBe('1815-12-10')
        ->and($actor->status)->toBeFalse()
        ->and($actor->is_public)->toBeFalse();
});

it('ajeno no cambia is_public/status sin permiso futuro (gancho)', function () {
    expect(UserProfileAuthorization::visibilityPermissionSlug())->toBeNull();

    $actor = actingEditor($this->company);
    $member = User::factory()->create([
        'is_public' => true,
        'status' => true,
        'name' => 'Keep',
    ]);
    $this->company->users()->attach($member->id);

    expect(UserProfileAuthorization::canChangeVisibilityFlags($actor, $member))->toBeFalse();

    $this->put(route('admin.company-users.update', $member), [
        'name' => 'Renamed',
        'email' => $member->email,
        'status' => false,
        'is_public' => false,
    ])->assertRedirect();

    $member->refresh();
    expect($member->name)->toBe('Renamed')
        ->and($member->status)->toBeTrue()
        ->and($member->is_public)->toBeTrue();
});

it('self cambia contraseña; ajeno y SA no pueden cambiar la de otro', function () {
    $actor = actingEditor($this->company);
    $member = User::factory()->create(['password' => Hash::make('password')]);
    $this->company->users()->attach($member->id);

    $this->put(route('admin.profile.password'), [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect();

    expect(Hash::check('new-password-123', $actor->fresh()->getAuthPassword()))->toBeTrue();

    $this->put(route('admin.company-users.password', $member), [
        'current_password' => 'password',
        'password' => 'hijacked-pass-1',
        'password_confirmation' => 'hijacked-pass-1',
    ])->assertForbidden();

    $sa = User::factory()->create(['password' => Hash::make('password')]);
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $this->put(route('admin.company-users.password', $member), [
        'current_password' => 'password',
        'password' => 'sa-hijack-pass1',
        'password_confirmation' => 'sa-hijack-pass1',
    ])->assertForbidden();

    expect(Hash::check('password', $member->fresh()->getAuthPassword()))->toBeTrue();
});

it('SA puede editar perfil ajeno en perímetro pero no su password', function () {
    $sa = User::factory()->create(['password' => Hash::make('password')]);
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $member = User::factory()->create(['name' => 'Target', 'email' => 'target@example.test']);
    $this->company->users()->attach($member->id);

    $this->get(route('admin.company-users.edit', $member))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('can.change_password', false));

    $this->put(route('admin.company-users.update', $member), [
        'name' => 'TargetEdited',
        'email' => 'target@example.test',
    ])->assertRedirect();

    expect($member->fresh()->name)->toBe('TargetEdited');
});

it('SA con sesión en A no edita ni actualiza user solo de B (perímetro)', function () {
    $sa = User::factory()->create(['password' => Hash::make('password')]);
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $foreign = User::factory()->create([
        'name' => 'OnlyInB',
        'email' => 'only-b@example.test',
    ]);
    $this->other->users()->attach($foreign->id);

    $this->get(route('admin.company-users.edit', $foreign))->assertForbidden();

    $this->put(route('admin.company-users.update', $foreign), [
        'name' => 'HackedBySa',
        'email' => 'only-b@example.test',
    ])->assertForbidden();

    expect($foreign->fresh()->name)->toBe('OnlyInB');
});
