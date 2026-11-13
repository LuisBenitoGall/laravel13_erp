<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
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

    $usuarios = app(CreateModule::class)->handle([
        'name' => 'Usuarios',
        'slug' => 'usuarios',
        'label' => ['es' => 'Usuarios'],
        'color' => '#0369a1',
        'icon' => 'users',
        'level' => ModuleLevel::Basic->value,
        'status' => ModuleStatus::Active->value,
    ]);

    app(CreateFunctionality::class)->handle([
        'module_id' => $usuarios->id,
        'name' => 'Company users',
        'slug' => 'company-users',
        'label' => ['es' => 'Usuarios empresa'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'usuarios');
    enableModuleForCompany($this->other, 'usuarios');
    MenuCatalogCache::forget();
});

function actingEmailManager(Company $company, bool $withUpdate = true): User
{
    $user = User::factory()->create([
        'status' => true,
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

it('expone rutas profile y company-users emails; stub Http eliminado', function () {
    expect(Route::has('admin.profile.emails.store'))->toBeTrue()
        ->and(Route::has('admin.profile.emails.destroy'))->toBeTrue()
        ->and(Route::has('admin.company-users.emails.store'))->toBeTrue()
        ->and(Route::has('admin.company-users.emails.destroy'))->toBeTrue()
        ->and(file_exists(app_path('Http/Controllers/UserEmailController.php')))->toBeFalse();
});

it('permite N emails del mismo user tras migración', function () {
    $user = User::factory()->create();

    UserEmail::factory()->create(['user_id' => $user->id, 'email' => 'a@example.test']);
    UserEmail::factory()->create(['user_id' => $user->id, 'email' => 'b@example.test']);

    expect($user->emails()->count())->toBe(2);
});

it('self crea y elimina email adicional en profile', function () {
    $actor = actingEmailManager($this->company);

    $this->post(route('admin.profile.emails.store'), [
        'email' => 'extra@example.test',
        'description' => 'Personal',
    ])->assertRedirect();

    $email = UserEmail::query()->where('email', 'extra@example.test')->first();
    expect($email)->not->toBeNull()
        ->and($email->user_id)->toBe($actor->id)
        ->and($email->description)->toBe('Personal');

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->has('emails', 1)
            ->where('emails.0.email', 'extra@example.test'));

    $this->delete(route('admin.profile.emails.destroy', $email))->assertRedirect();
    expect(UserEmail::query()->whereKey($email->id)->exists())->toBeFalse();
});

it('rechaza duplicado e igual al principal', function () {
    $actor = actingEmailManager($this->company);
    UserEmail::factory()->create(['user_id' => $actor->id, 'email' => 'taken@example.test']);

    $this->post(route('admin.profile.emails.store'), [
        'email' => 'taken@example.test',
    ])->assertInvalid(['email']);

    $this->post(route('admin.profile.emails.store'), [
        'email' => $actor->email,
    ])->assertInvalid(['email']);
});

it('company-users: alta ajena con update; 403 sin update; perímetro SA', function () {
    $actor = actingEmailManager($this->company);
    $member = User::factory()->create(['email' => 'member@example.test']);
    $this->company->users()->attach($member->id);

    $this->post(route('admin.company-users.emails.store', $member), [
        'email' => 'member-extra@example.test',
        'description' => null,
    ])->assertRedirect();

    $memberEmail = UserEmail::query()->where('email', 'member-extra@example.test')->firstOrFail();
    expect($memberEmail->user_id)->toBe($member->id);

    $this->delete(route('admin.company-users.emails.destroy', [$member, $memberEmail]))
        ->assertRedirect();
    expect(UserEmail::query()->whereKey($memberEmail->id)->exists())->toBeFalse();

    $this->post(route('admin.company-users.emails.store', $member), [
        'email' => 'member-extra-2@example.test',
    ])->assertRedirect();
    $memberEmail = UserEmail::query()->where('email', 'member-extra-2@example.test')->firstOrFail();

    $noUpdate = actingEmailManager($this->company, withUpdate: false);
    $this->actingAs($noUpdate);
    session(['current_company_id' => $this->company->id]);
    withCurrentCompany($this->company);

    $this->post(route('admin.company-users.emails.store', $member), [
        'email' => 'denied@example.test',
    ])->assertForbidden();
    $this->delete(route('admin.company-users.emails.destroy', [$member, $memberEmail]))
        ->assertForbidden();

    $sa = User::factory()->create();
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $foreign = User::factory()->create(['email' => 'foreign@example.test']);
    $this->other->users()->attach($foreign->id);
    $foreignEmail = UserEmail::factory()->create([
        'user_id' => $foreign->id,
        'email' => 'foreign-extra@example.test',
    ]);

    $this->post(route('admin.company-users.emails.store', $foreign), [
        'email' => 'sa-hack@example.test',
    ])->assertForbidden();
    $this->delete(route('admin.company-users.emails.destroy', [$foreign, $foreignEmail]))
        ->assertForbidden();

    expect(UserEmail::query()->where('email', 'sa-hack@example.test')->exists())->toBeFalse()
        ->and(UserEmail::query()->whereKey($foreignEmail->id)->exists())->toBeTrue();
});

it('UI contacts panel no usa placeholder de emails y no tiene Edit', function () {
    $source = file_get_contents(resource_path('js/Pages/Users/components/ContactsEmailsPanel.tsx'));
    expect($source)->toContain('mailto:')
        ->and($source)->toContain('copyText')
        ->and($source)->toContain('ConfirmDialog')
        ->and($source)->not->toContain('pestana_vacia_proximamente')
        ->and($source)->not->toContain('editar_email');

    $edit = file_get_contents(resource_path('js/Pages/Users/Edit.tsx'));
    expect($edit)->toContain('ContactsEmailsPanel');
});
