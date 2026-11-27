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
use App\Domains\Admin\Support\UserQuickViewData;
use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    app(CreateFunctionality::class)->handle([
        'module_id' => $this->usuarios->id,
        'name' => 'All users',
        'slug' => 'all-users',
        'label' => ['es' => 'Todos los usuarios'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    enableModuleForCompany($this->company, 'usuarios');
    enableModuleForCompany($this->other, 'usuarios');

    app(EnsureCompanyDefaultRoles::class)->handle($this->company);
    MenuCatalogCache::forget();
});

function actingCompanyUsersViewer(Company $company, bool $withUpdate = true): User
{
    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);

    $perms = [
        Permission::findOrCreate('users.access'),
        Permission::findOrCreate('company-users.view'),
        Permission::findOrCreate('company-users.viewAny'),
    ];

    if ($withUpdate) {
        $perms[] = Permission::findOrCreate('company-users.update');
    }

    $user->givePermissionTo($perms);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');
    setPermissionsTeamId(null);

    return $user;
}

function actingAllUsersViewer(Company $company): User
{
    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $user->givePermissionTo([
        Permission::findOrCreate('users.access'),
        Permission::findOrCreate('all-users.view'),
    ]);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');
    setPermissionsTeamId(null);

    return $user;
}

it('UserQuickViewData proyecta shape de readout con can_edit', function () {
    $company = $this->company;
    $subject = User::factory()->create(['name' => 'Nia', 'surname' => 'Quick']);
    $subject->companies()->attach($company->id);

    UserEmail::query()->create([
        'user_id' => $subject->id,
        'email' => 'nia-extra@example.com',
        'description' => 'nota',
    ]);

    $payload = app(UserQuickViewData::class)->build($subject, true);

    expect($payload)
        ->toHaveKeys(['user', 'emails', 'phones', 'companies', 'avatar_url', 'can_edit'])
        ->and($payload['user'])->toMatchArray([
            'name' => 'Nia',
            'surname' => 'Quick',
        ])
        ->and($payload['user'])->not->toHaveKey('nif')
        ->and($payload['emails'][0]['email'])->toBe('nia-extra@example.com')
        ->and($payload['can_edit'])->toBeTrue();
});

it('company-users quick autoriza view, can_edit y aísla tenant', function () {
    $member = User::factory()->create(['name' => 'Member']);
    $member->companies()->attach($this->company->id);

    $foreign = User::factory()->create(['name' => 'Foreign']);
    $foreign->companies()->attach($this->other->id);

    $actor = actingCompanyUsersViewer($this->company, withUpdate: true);

    $this->getJson(route('admin.company-users.quick', $member))
        ->assertOk()
        ->assertJsonPath('user.name', 'Member')
        ->assertJsonPath('can_edit', true);

    $viewerOnly = actingCompanyUsersViewer($this->company, withUpdate: false);

    $this->actingAs($viewerOnly);
    session(['current_company_id' => $this->company->id]);
    withCurrentCompany($this->company);

    $this->getJson(route('admin.company-users.quick', $member))
        ->assertOk()
        ->assertJsonPath('can_edit', false);

    // R-TEN-05: user vinculado solo a otra company → 403
    $this->getJson(route('admin.company-users.quick', $foreign))
        ->assertForbidden();
});

it('company-users quick 403 sin permiso view', function () {
    $member = User::factory()->create();
    $member->companies()->attach($this->company->id);

    $actor = User::factory()->create(['status' => true]);
    actingWithCompany($actor, $this->company);

    $this->getJson(route('admin.company-users.quick', $member))
        ->assertForbidden();
});

it('all-users quick solo públicos y sin can_edit', function () {
    $public = User::factory()->create([
        'name' => 'Public',
        'is_public' => true,
    ]);
    $private = User::factory()->create([
        'name' => 'Private',
        'is_public' => false,
    ]);

    actingAllUsersViewer($this->company);

    $this->getJson(route('admin.all-users.quick', $public))
        ->assertOk()
        ->assertJsonPath('user.name', 'Public')
        ->assertJsonPath('can_edit', false);

    $this->getJson(route('admin.all-users.quick', $private))
        ->assertNotFound();
});

it('all-users quick 403 sin permiso', function () {
    $public = User::factory()->create(['is_public' => true]);
    $actor = User::factory()->create(['status' => true]);
    actingWithCompany($actor, $this->company);

    $this->getJson(route('admin.all-users.quick', $public))
        ->assertForbidden();
});

it('Indexes y panel cablean ver detalle, Sheet y cierre solo X', function () {
    $companyIndex = (string) file_get_contents(resource_path('js/Pages/Admin/CompanyUsers/Index.tsx'));
    $allIndex = (string) file_get_contents(resource_path('js/Pages/Admin/AllUsers/Index.tsx'));
    $panel = (string) file_get_contents(resource_path('js/Components/Users/UserQuickViewPanel.tsx'));
    $sheet = (string) file_get_contents(resource_path('js/Components/ui/sheet.tsx'));

    expect($companyIndex)->toContain('nameQuickViewFormatter')
        ->and($companyIndex)->toContain('UserQuickViewPanel')
        ->and($companyIndex)->toContain('can_edit')
        ->and($companyIndex)->toContain('const rowActions = useCallback')
        ->and($companyIndex)->toContain('const massActions = useMemo')
        ->and($companyIndex)->toContain('const columns = useMemo')
        ->and($allIndex)->toContain('nameQuickViewFormatter')
        ->and($allIndex)->toContain('editHref={null}')
        ->and($allIndex)->toContain('const rowActions = useCallback')
        ->and($allIndex)->toContain('const columns = useMemo')
        ->and($panel)->toContain('onInteractOutside={preventDismiss}')
        ->and($panel)->toContain('onEscapeKeyDown={preventDismiss}')
        ->and($panel)->toContain('user-quick-view-close')
        ->and($panel)->toContain('UserProfileReadout')
        ->and($sheet)->toContain("side = 'right'")
        ->and($sheet)->toContain('pointer-events-none');
});
