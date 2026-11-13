<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\CreateModule;
use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Enums\PhoneType;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Role;
use App\Domains\Admin\Support\MenuCatalogCache;
use App\Models\Phone;
use App\Models\User;
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

function actingPhoneManager(Company $company, bool $withUpdate = true): User
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

it('expone rutas profile y company-users phones', function () {
    expect(Route::has('admin.profile.phones.store'))->toBeTrue()
        ->and(Route::has('admin.profile.phones.update'))->toBeTrue()
        ->and(Route::has('admin.profile.phones.destroy'))->toBeTrue()
        ->and(Route::has('admin.profile.phones.mark-primary'))->toBeTrue()
        ->and(Route::has('admin.company-users.phones.store'))->toBeTrue()
        ->and(Route::has('admin.company-users.phones.update'))->toBeTrue()
        ->and(Route::has('admin.company-users.phones.destroy'))->toBeTrue()
        ->and(Route::has('admin.company-users.phones.mark-primary'))->toBeTrue();
});

it('self crea edita elimina y marca primario en profile; country_id null', function () {
    $actor = actingPhoneManager($this->company);

    $this->post(route('admin.profile.phones.store'), [
        'e164' => '+34600111222',
        'ext' => null,
        'type' => PhoneType::Mobile->value,
        'label' => 'Móvil',
        'notes' => 'Personal',
        'is_whatsapp' => true,
        'is_primary' => true,
    ])->assertRedirect();

    $phone = Phone::query()->where('e164', '+34600111222')->first();
    expect($phone)->not->toBeNull()
        ->and($phone->phoneable_id)->toBe($actor->id)
        ->and($phone->phoneable_type)->toBe($actor->getMorphClass())
        ->and($phone->country_id)->toBeNull()
        ->and($phone->is_primary)->toBeTrue()
        ->and($phone->is_whatsapp)->toBeTrue()
        ->and($phone->is_verified)->toBeFalse();

    $other = Phone::factory()->create([
        'phoneable_type' => $actor->getMorphClass(),
        'phoneable_id' => $actor->id,
        'e164' => '+34911122333',
        'type' => PhoneType::Landline,
        'is_primary' => false,
    ]);

    $this->post(route('admin.profile.phones.mark-primary', $other))->assertRedirect();
    expect($other->fresh()->is_primary)->toBeTrue()
        ->and($phone->fresh()->is_primary)->toBeFalse();

    $this->put(route('admin.profile.phones.update', $phone), [
        'e164' => '+34600111222',
        'ext' => '12',
        'type' => PhoneType::Other->value,
        'label' => 'Alt',
        'notes' => null,
        'is_whatsapp' => false,
        'is_primary' => true,
    ])->assertRedirect();

    expect($phone->fresh()->ext)->toBe('12')
        ->and($phone->fresh()->type)->toBe(PhoneType::Other)
        ->and($phone->fresh()->is_primary)->toBeTrue()
        ->and($other->fresh()->is_primary)->toBeFalse()
        ->and($phone->fresh()->country_id)->toBeNull();

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->has('phones', 2)
            ->where('phones.0.is_primary', true));

    $this->delete(route('admin.profile.phones.destroy', $phone))->assertRedirect();
    expect(Phone::query()->whereKey($phone->id)->exists())->toBeFalse()
        ->and(Phone::withTrashed()->whereKey($phone->id)->exists())->toBeTrue();
});

it('rechaza e164 duplicado activo y permite recrear tras soft delete', function () {
    $actor = actingPhoneManager($this->company);
    Phone::factory()->create([
        'phoneable_type' => $actor->getMorphClass(),
        'phoneable_id' => $actor->id,
        'e164' => '+34600999888',
    ]);

    $this->post(route('admin.profile.phones.store'), [
        'e164' => '+34600999888',
        'type' => PhoneType::Mobile->value,
    ])->assertInvalid(['e164']);

    $existing = Phone::query()->where('e164', '+34600999888')->firstOrFail();
    $existing->delete();

    $this->post(route('admin.profile.phones.store'), [
        'e164' => '+34600999888',
        'type' => PhoneType::Mobile->value,
        'is_primary' => false,
    ])->assertRedirect();

    expect(Phone::query()->where('e164', '+34600999888')->count())->toBe(1);
});

it('company-users: alta ajena con update; 403 sin update; perímetro SA', function () {
    $actor = actingPhoneManager($this->company);
    $member = User::factory()->create(['email' => 'member-phone@example.test']);
    $this->company->users()->attach($member->id);

    $this->post(route('admin.company-users.phones.store', $member), [
        'e164' => '+34611122334',
        'type' => PhoneType::Mobile->value,
        'is_primary' => true,
        'is_whatsapp' => false,
    ])->assertRedirect();

    $memberPhone = Phone::query()->where('e164', '+34611122334')->firstOrFail();
    expect($memberPhone->phoneable_id)->toBe($member->id)
        ->and($memberPhone->country_id)->toBeNull();

    $this->put(route('admin.company-users.phones.update', [$member, $memberPhone]), [
        'e164' => '+34611122334',
        'type' => PhoneType::Landline->value,
        'is_primary' => true,
        'is_whatsapp' => true,
    ])->assertRedirect();

    $this->post(route('admin.company-users.phones.mark-primary', [$member, $memberPhone]))
        ->assertRedirect();

    $this->delete(route('admin.company-users.phones.destroy', [$member, $memberPhone]))
        ->assertRedirect();
    expect(Phone::query()->whereKey($memberPhone->id)->exists())->toBeFalse();

    $this->post(route('admin.company-users.phones.store', $member), [
        'e164' => '+34611122335',
        'type' => PhoneType::Mobile->value,
    ])->assertRedirect();
    $memberPhone = Phone::query()->where('e164', '+34611122335')->firstOrFail();

    $noUpdate = actingPhoneManager($this->company, withUpdate: false);
    $this->actingAs($noUpdate);
    session(['current_company_id' => $this->company->id]);
    withCurrentCompany($this->company);

    $this->post(route('admin.company-users.phones.store', $member), [
        'e164' => '+34600000001',
        'type' => PhoneType::Mobile->value,
    ])->assertForbidden();
    $this->put(route('admin.company-users.phones.update', [$member, $memberPhone]), [
        'e164' => '+34611122335',
        'type' => PhoneType::Fax->value,
    ])->assertForbidden();
    $this->delete(route('admin.company-users.phones.destroy', [$member, $memberPhone]))
        ->assertForbidden();

    $sa = User::factory()->create();
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $foreign = User::factory()->create(['email' => 'foreign-phone@example.test']);
    $this->other->users()->attach($foreign->id);
    $foreignPhone = Phone::factory()->create([
        'phoneable_type' => $foreign->getMorphClass(),
        'phoneable_id' => $foreign->id,
        'e164' => '+34700111222',
    ]);

    $this->post(route('admin.company-users.phones.store', $foreign), [
        'e164' => '+34700999888',
        'type' => PhoneType::Mobile->value,
    ])->assertForbidden();
    $this->delete(route('admin.company-users.phones.destroy', [$foreign, $foreignPhone]))
        ->assertForbidden();

    expect(Phone::query()->where('e164', '+34700999888')->exists())->toBeFalse()
        ->and(Phone::query()->whereKey($foreignPhone->id)->exists())->toBeTrue();
});

it('UI phones panel sin placeholder y con acciones acordadas', function () {
    $panel = file_get_contents(resource_path('js/Pages/Users/components/ContactsPhonesPanel.tsx'));
    $emails = file_get_contents(resource_path('js/Pages/Users/components/ContactsEmailsPanel.tsx'));
    $edit = file_get_contents(resource_path('js/Pages/Users/Edit.tsx'));

    expect($panel)->toContain('tel:')
        ->and($panel)->toContain('wa.me')
        ->and($panel)->toContain('copyText')
        ->and($panel)->toContain('markPrimaryUrl')
        ->and($panel)->toContain('ConfirmDialog')
        ->and($panel)->not->toContain('verificar')
        ->and($emails)->toContain('ContactsPhonesPanel')
        ->and($emails)->not->toContain('telefonos_placeholder')
        ->and($edit)->toContain('phonesStoreUrl');
});
