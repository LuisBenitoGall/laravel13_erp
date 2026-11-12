<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);
    Storage::fake('public');

    Module::factory()->create([
        'slug' => 'empresas',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
});

function actingCompanyEditor(Company $company): User
{
    enableModuleForCompany($company, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $company);

    setPermissionsTeamId($company->id);
    $user->givePermissionTo([
        Permission::findOrCreate('empresas.access'),
        Permission::findOrCreate('companies.view'),
        Permission::findOrCreate('companies.update'),
    ]);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('actualiza info regenera slug y rechaza nif invalido o duplicado', function () {
    $company = Company::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active,
    ]);
    Company::factory()->create([
        'name' => 'Other',
        'slug' => 'other',
        'nif' => 'X1234567L',
        'status' => CompanyStatus::Active,
    ]);
    actingCompanyEditor($company);

    $this->put(route('partners.companies.update', $company), [
        'name' => 'New Brand Co',
        'tradename' => 'New Brand',
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active->value,
    ])->assertRedirect();

    $company->refresh();
    expect($company->name)->toBe('New Brand Co')
        ->and($company->slug)->toBe('new-brand-co')
        ->and($company->nif)->toBe('12345678Z');

    $this->from(route('partners.companies.edit', $company))
        ->put(route('partners.companies.update', $company), [
            'name' => 'New Brand Co',
            'nif' => 'B12345678',
            'status' => CompanyStatus::Active->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('nif');

    $this->from(route('partners.companies.edit', $company))
        ->put(route('partners.companies.update', $company), [
            'name' => 'New Brand Co',
            'nif' => 'X1234567L',
            'status' => CompanyStatus::Active->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('nif');
});

it('regenera slug con sufijo si colisiona con otra company', function () {
    $company = Company::factory()->create([
        'name' => 'Alpha',
        'slug' => 'alpha',
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active,
    ]);
    Company::factory()->create([
        'name' => 'Taken',
        'slug' => 'twin-name',
        'nif' => 'X1234567L',
        'status' => CompanyStatus::Active,
    ]);
    actingCompanyEditor($company);

    $this->put(route('partners.companies.update', $company), [
        'name' => 'Twin Name',
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active->value,
    ])->assertRedirect();

    expect($company->fresh()->slug)->toStartWith('twin-name-')
        ->and($company->fresh()->slug)->not->toBe('twin-name');
});

it('ignora slug enviado por el cliente en Update', function () {
    $company = Company::factory()->create([
        'name' => 'Alpha Co',
        'slug' => 'alpha-co',
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active,
    ]);
    actingCompanyEditor($company);

    $this->put(route('partners.companies.update', $company), [
        'name' => 'Alpha Co',
        'tradename' => null,
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active->value,
        'slug' => 'forced-by-client',
    ])->assertRedirect();

    expect($company->fresh()->slug)->toBe('alpha-co')
        ->and($company->fresh()->slug)->not->toBe('forced-by-client');
});

it('rechaza update de company no vinculada', function () {
    $linked = Company::factory()->create(['status' => CompanyStatus::Active, 'nif' => '12345678Z']);
    $foreign = Company::factory()->create(['status' => CompanyStatus::Active, 'nif' => 'X1234567L']);
    actingCompanyEditor($linked);

    $this->put(route('partners.companies.update', $foreign), [
        'name' => 'Hacked',
        'nif' => 'X1234567L',
        'status' => CompanyStatus::Active->value,
    ])->assertForbidden();
});

it('sube logo y actualiza tenant.logo_url solo si es CurrentCompany', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active, 'nif' => '12345678Z']);
    $other = Company::factory()->create(['status' => CompanyStatus::Active, 'nif' => 'X1234567L']);
    $actor = actingCompanyEditor($session);
    $actor->companies()->attach($other->id);

    $file = UploadedFile::fake()->image('logo.png', 40, 40);

    $this->post(route('partners.companies.logo', $session), [
        'logo' => $file,
    ])->assertRedirect();

    $session->refresh();
    expect($session->logo)->not->toBeNull();
    Storage::disk('public')->assertExists($session->logo);

    $this->get(route('partners.companies.edit', $session))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tenant.id', $session->id)
            ->where('tenant.logo_url', fn ($url) => is_string($url) && str_contains($url, $session->logo)));

    session(['current_company_id' => $session->id]);
    withCurrentCompany($session);

    $otherFile = UploadedFile::fake()->image('other.png', 40, 40);
    $this->post(route('partners.companies.logo', $other), [
        'logo' => $otherFile,
    ])->assertRedirect();

    $other->refresh();
    expect($other->logo)->not->toBeNull();

    $this->get(route('partners.companies.edit', $other))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tenant.id', $session->id)
            ->where('tenant.logo_url', fn ($url) => is_string($url) && str_contains((string) $url, (string) $session->logo)));
});

it('rechaza logo por encima del limite', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active, 'nif' => '12345678Z']);
    actingCompanyEditor($company);

    $heavy = UploadedFile::fake()->image('huge.jpg')->size(600);

    $this->from(route('partners.companies.edit', ['company' => $company, 'tab' => 'images']))
        ->post(route('partners.companies.logo', $company), [
            'logo' => $heavy,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('logo');
});

it('Edit no expone members_url ni slug/logo de texto', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active, 'nif' => '12345678Z']);
    actingCompanyEditor($company);

    $this->get(route('partners.companies.edit', $company))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Partners/Companies/Edit')
            ->has('company')
            ->missing('members_url'));

    $edit = file_get_contents(resource_path('js/Pages/Partners/Companies/Edit.tsx'));
    $form = file_get_contents(resource_path('js/Pages/Partners/Companies/components/CompanyForm.tsx'));

    expect($edit)->toContain('RecordEditLayout')
        ->and($edit)->toContain('CompanyProfileReadout')
        ->and($edit)->toContain("id: 'info'")
        ->and($edit)->toContain("id: 'images'")
        ->and($edit)->not->toContain('gestionar_miembros')
        ->and($edit)->not->toContain('members_url')
        ->and($form)->not->toContain("t('slug')")
        ->and($form)->not->toContain("t('logo_url')")
        ->and($form)->toContain('maxLength={12}');
});
