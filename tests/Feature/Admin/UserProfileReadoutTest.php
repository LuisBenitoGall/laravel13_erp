<?php

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Support\UserEditPageData;
use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
});

it('profile Edit incluye companies ordenadas por nombre en el payload', function () {
    $alpha = Company::factory()->create([
        'name' => 'Alpha Co',
        'status' => CompanyStatus::Active,
    ]);
    $zeta = Company::factory()->create([
        'name' => 'Zeta Co',
        'status' => CompanyStatus::Active,
    ]);

    $user = User::factory()->create([
        'name' => 'Ada',
        'surname' => 'Lovelace',
        'email' => 'ada-readout@example.com',
    ]);
    $user->companies()->attach([$zeta->id, $alpha->id]);

    actingWithCompany($user, $alpha);

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->where('context', 'profile')
            ->has('companies', 2)
            ->where('companies.0.id', $alpha->id)
            ->where('companies.0.name', 'Alpha Co')
            ->where('companies.0.href', null)
            ->where('companies.1.id', $zeta->id)
            ->where('companies.1.name', 'Zeta Co'));
});

it('UserEditPageData companies vacío si el user no tiene vínculos', function () {
    $actor = User::factory()->create();
    $subject = User::factory()->create();

    $data = app(UserEditPageData::class)->build($subject, $actor, 'profile');

    expect($data['companies'])->toBe([]);
});

it('UserProfileReadout no muestra nif/fecha/activo/publico y copia emails sin observación', function () {
    $source = (string) file_get_contents(resource_path('js/Components/Users/UserProfileReadout.tsx'));
    $edit = (string) file_get_contents(resource_path('js/Pages/Users/Edit.tsx'));
    $es = json_decode((string) file_get_contents(lang_path('es.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($source)->toContain('data-testid="user-profile-readout"')
        ->and($source)->toContain('data-testid="user-profile-readout-emails"')
        ->and($source)->toContain('data-testid="user-profile-readout-companies"')
        ->and($source)->toContain('empresas_vinculadas')
        ->and($source)->toContain('email_copiado')
        ->and($source)->toContain('copyText')
        ->and($source)->toContain('companies.length > 0')
        ->and($source)->not->toContain("t('nif')")
        ->and($source)->not->toContain("t('fecha_nacimiento')")
        ->and($source)->not->toContain("t('activo')")
        ->and($source)->not->toContain("t('perfil_publico')")
        ->and($source)->not->toContain('item.description')
        ->and($edit)->toContain('companies={companies}')
        ->and($edit)->not->toContain('birthDateFormatted')
        ->and($es)->toHaveKey('empresas_vinculadas')
        ->and($es)->toHaveKey('email_copiado');
});

it('emails del payload siguen trayendo description pero el readout no la usa en UI', function () {
    $company = Company::factory()->create(['status' => CompanyStatus::Active]);
    $user = User::factory()->create();
    actingWithCompany($user, $company);

    UserEmail::query()->create([
        'user_id' => $user->id,
        'email' => 'extra@example.com',
        'description' => 'Observación secreta',
    ]);

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->has('emails', 1)
            ->where('emails.0.email', 'extra@example.com')
            ->where('emails.0.description', 'Observación secreta'));
});
