<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Role::findOrCreate(Role::SUPER_ADMIN);

    Module::factory()->create([
        'slug' => 'empresas',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
});

function actingCompanyCreator(Company $sessionCompany): User
{
    enableModuleForCompany($sessionCompany, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $sessionCompany);

    setPermissionsTeamId($sessionCompany->id);
    $user->givePermissionTo([
        Permission::findOrCreate('empresas.access'),
        Permission::findOrCreate('companies.view'),
        Permission::findOrCreate('companies.create'),
    ]);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('crea company con slug automatico y redirige a Edit', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active, 'slug' => 'session-co']);
    actingCompanyCreator($session);

    $response = $this->post(route('partners.companies.store'), [
        'name' => 'Acme Corp',
        'tradename' => 'Acme SL',
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active->value,
    ]);

    $created = Company::query()->where('nif', '12345678Z')->first();
    expect($created)->not->toBeNull()
        ->and($created->slug)->toBe('acme-corp')
        ->and($created->name)->toBe('Acme Corp')
        ->and($created->tradename)->toBe('Acme SL');

    $response->assertRedirect(route('partners.companies.edit', $created));
});

it('genera slug con sufijo ante colision', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active, 'slug' => 'session-co']);
    Company::factory()->create(['slug' => 'twin-co', 'status' => CompanyStatus::Active]);
    actingCompanyCreator($session);

    $this->post(route('partners.companies.store'), [
        'name' => 'Twin Co',
        'tradename' => null,
        'nif' => '12345678Z',
        'status' => CompanyStatus::Draft->value,
    ])->assertRedirect();

    $created = Company::query()->where('nif', '12345678Z')->firstOrFail();
    expect($created->slug)->not->toBe('twin-co')
        ->and($created->slug)->toStartWith('twin-co-');
});

it('rechaza nif formato invalido y duplicado', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active, 'slug' => 'session-co']);
    Company::factory()->create([
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active,
        'slug' => 'other-co',
    ]);
    actingCompanyCreator($session);

    $this->from(route('partners.companies.create'))
        ->post(route('partners.companies.store'), [
            'name' => 'Bad Nif',
            'nif' => 'B12345678',
            'status' => CompanyStatus::Active->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('nif');

    $this->from(route('partners.companies.create'))
        ->post(route('partners.companies.store'), [
            'name' => 'Dup Nif',
            'nif' => '12345678Z',
            'status' => CompanyStatus::Active->value,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('nif');

    expect(Company::query()->where('name', 'Bad Nif')->exists())->toBeFalse()
        ->and(Company::query()->where('name', 'Dup Nif')->exists())->toBeFalse();
});

it('ignora slug y logo enviados por el cliente en Create', function () {
    $session = Company::factory()->create(['status' => CompanyStatus::Active, 'slug' => 'session-co']);
    actingCompanyCreator($session);

    $this->post(route('partners.companies.store'), [
        'name' => 'Client Slug Co',
        'slug' => 'hacked-slug',
        'tradename' => null,
        'nif' => '12345678Z',
        'status' => CompanyStatus::Active->value,
        'logo' => 'http://evil.example/logo.png',
    ])->assertRedirect();

    $created = Company::query()->where('nif', '12345678Z')->firstOrFail();
    expect($created->slug)->toBe('client-slug-co')
        ->and($created->logo)->toBeNull();
});

it('UI Create solo name tradename nif status sin slug ni logo', function () {
    $create = file_get_contents(resource_path('js/Pages/Partners/Companies/Create.tsx'));
    $form = file_get_contents(resource_path('js/Pages/Partners/Companies/components/CompanyForm.tsx'));

    expect($create)->toContain('mode="create"')
        ->and($create)->toContain("optionKey: 'mis_empresas'")
        ->and($create)->toContain("suboptionKey: 'empresa_nueva'")
        ->and($form)->toContain("mode?: 'create' | 'edit'")
        ->and($form)->toContain('maxLength={12}')
        ->and($form)->toContain("from '@/Components/ui/button'")
        ->and($form)->not->toContain("t('slug')")
        ->and($form)->not->toContain("t('logo_url')");
});
