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
use App\Domains\Admin\Support\UserEditPageData;
use App\Models\User;
use App\Models\UserDoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
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

function actingDocManager(Company $company, bool $withUpdate = true): User
{
    $user = User::factory()->create(['status' => true]);
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

it('expone rutas profile y company-users docs', function () {
    expect(Route::has('admin.profile.docs.store'))->toBeTrue()
        ->and(Route::has('admin.profile.docs.download'))->toBeTrue()
        ->and(Route::has('admin.profile.docs.destroy'))->toBeTrue()
        ->and(Route::has('admin.company-users.docs.store'))->toBeTrue()
        ->and(Route::has('admin.company-users.docs.download'))->toBeTrue()
        ->and(Route::has('admin.company-users.docs.destroy'))->toBeTrue();
});

it('self sube lista descarga y elimina doc en disco local', function () {
    $actor = actingDocManager($this->company);
    $file = UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf');

    $this->post(route('admin.profile.docs.store'), [
        'docs' => [$file],
    ])->assertRedirect();

    $doc = UserDoc::query()->where('user_id', $actor->id)->first();
    expect($doc)->not->toBeNull()
        ->and($doc->original_name)->toBe('contrato.pdf')
        ->and(Storage::disk('local')->exists($doc->path))->toBeTrue();

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->has('docs', 1)
            ->where('docs.0.original_name', 'contrato.pdf')
            ->where('can.manage_docs', true));

    $this->get(route('admin.profile.docs.download', $doc))
        ->assertOk();

    $path = $doc->path;
    $this->delete(route('admin.profile.docs.destroy', $doc))->assertRedirect();
    expect(UserDoc::query()->whereKey($doc->id)->exists())->toBeFalse()
        ->and(UserDoc::withTrashed()->whereKey($doc->id)->exists())->toBeTrue()
        ->and(Storage::disk('local')->exists($path))->toBeFalse();
});

it('rechaza formato peso y limite 30', function () {
    $actor = actingDocManager($this->company);

    $this->post(route('admin.profile.docs.store'), [
        'docs' => [UploadedFile::fake()->image('photo.jpg')->size(50)],
    ])->assertInvalid(['docs.0']);

    $this->post(route('admin.profile.docs.store'), [
        'docs' => [UploadedFile::fake()->create('big.pdf', 21000, 'application/pdf')],
    ])->assertInvalid(['docs.0']);

    for ($i = 0; $i < 30; $i++) {
        UserDoc::factory()->create([
            'user_id' => $actor->id,
            'path' => "users/{$actor->id}/docs/seed-{$i}.pdf",
        ]);
    }

    $this->post(route('admin.profile.docs.store'), [
        'docs' => [UploadedFile::fake()->create('overflow.pdf', 50, 'application/pdf')],
    ])->assertInvalid(['docs']);
});

it('company-users: mutaciones con update; 403 sin update; perímetro SA download', function () {
    $actor = actingDocManager($this->company);
    $member = User::factory()->create(['email' => 'member-doc@example.test']);
    $this->company->users()->attach($member->id);

    $this->post(route('admin.company-users.docs.store', $member), [
        'docs' => [UploadedFile::fake()->create('m.pdf', 80, 'application/pdf')],
    ])->assertRedirect();

    $memberDoc = UserDoc::query()->where('user_id', $member->id)->firstOrFail();

    $this->get(route('admin.company-users.docs.download', [$member, $memberDoc]))
        ->assertOk();

    $noUpdate = actingDocManager($this->company, withUpdate: false);
    $this->actingAs($noUpdate);
    session(['current_company_id' => $this->company->id]);
    withCurrentCompany($this->company);

    $this->post(route('admin.company-users.docs.store', $member), [
        'docs' => [UploadedFile::fake()->create('denied.pdf', 40, 'application/pdf')],
    ])->assertForbidden();
    $this->get(route('admin.company-users.docs.download', [$member, $memberDoc]))
        ->assertForbidden();
    $this->delete(route('admin.company-users.docs.destroy', [$member, $memberDoc]))
        ->assertForbidden();

    $sa = User::factory()->create();
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $foreign = User::factory()->create(['email' => 'foreign-doc@example.test']);
    $this->other->users()->attach($foreign->id);
    $foreignDoc = UserDoc::factory()->create([
        'user_id' => $foreign->id,
        'path' => "users/{$foreign->id}/docs/foreign.pdf",
    ]);
    Storage::disk('local')->put($foreignDoc->path, 'x');

    $this->post(route('admin.company-users.docs.store', $foreign), [
        'docs' => [UploadedFile::fake()->create('sa.pdf', 40, 'application/pdf')],
    ])->assertForbidden();
    $this->get(route('admin.company-users.docs.download', [$foreign, $foreignDoc]))
        ->assertForbidden();
    $this->delete(route('admin.company-users.docs.destroy', [$foreign, $foreignDoc]))
        ->assertForbidden();

    expect(UserDoc::query()->where('user_id', $foreign->id)->count())->toBe(1)
        ->and(Storage::disk('local')->exists($foreignDoc->path))->toBeTrue();
});

it('manage_docs refleja Policy; Edit company exige update', function () {
    // company-users.edit autoriza updateCompanyUser → sin update no se abre Inertia Edit.
    $viewer = actingDocManager($this->company, withUpdate: false);
    $member = User::factory()->create(['email' => 'view-only-member@example.test']);
    $this->company->users()->attach($member->id);

    $this->get(route('admin.company-users.edit', $member))->assertForbidden();

    $props = app(UserEditPageData::class)->build($member, $viewer, 'company', $this->company);
    expect($props['can']['manage_docs'])->toBeFalse();

    $manager = actingDocManager($this->company, withUpdate: true);
    $this->get(route('admin.company-users.edit', $member))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->where('can.manage_docs', true));
});

it('UI docs panel usa Dropzone, download gated y tab tras imagenes', function () {
    $panel = file_get_contents(resource_path('js/Pages/Users/components/UserDocsPanel.tsx'));
    $edit = file_get_contents(resource_path('js/Pages/Users/Edit.tsx'));

    expect($panel)->toContain('Dropzone')
        ->and($panel)->toContain('downloadUrl')
        ->and($panel)->toContain('ConfirmDialog')
        ->and($panel)->toMatch('/canManage \? \([\s\S]*downloadUrl/')
        ->and($edit)->toContain("id: 'docs'")
        ->and($edit)->toContain('UserDocsPanel')
        ->and(strpos($edit, "id: 'images'") < strpos($edit, "id: 'docs'"))->toBeTrue()
        ->and(strpos($edit, "id: 'docs'") < strpos($edit, "id: 'companies'"))->toBeTrue();
});
