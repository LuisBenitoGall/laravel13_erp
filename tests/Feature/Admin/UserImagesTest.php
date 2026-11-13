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
use App\Models\UserImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
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

function actingImageManager(Company $company, bool $withUpdate = true): User
{
    $user = User::factory()->create(['status' => true, 'is_public' => true]);
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

it('expone rutas profile y company-users images', function () {
    expect(Route::has('admin.profile.images.store'))->toBeTrue()
        ->and(Route::has('admin.profile.images.destroy'))->toBeTrue()
        ->and(Route::has('admin.profile.images.mark-featured'))->toBeTrue()
        ->and(Route::has('admin.profile.images.public'))->toBeTrue()
        ->and(Route::has('admin.company-users.images.store'))->toBeTrue()
        ->and(Route::has('admin.company-users.images.destroy'))->toBeTrue()
        ->and(Route::has('admin.company-users.images.mark-featured'))->toBeTrue()
        ->and(Route::has('admin.company-users.images.public'))->toBeTrue();
});

it('self sube marca featured toggle public y elimina; avatar en props', function () {
    $actor = actingImageManager($this->company);
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100)->size(200);

    $this->post(route('admin.profile.images.store'), [
        'images' => [$file],
    ])->assertRedirect();

    $image = UserImage::query()->where('user_id', $actor->id)->first();
    expect($image)->not->toBeNull()
        ->and($image->featured)->toBeFalse()
        ->and($image->public)->toBeFalse()
        ->and(Storage::disk('public')->exists($image->image))->toBeTrue();

    $this->post(route('admin.profile.images.mark-featured', $image))->assertRedirect();
    expect($image->fresh()->featured)->toBeTrue();

    $second = UploadedFile::fake()->image('second.png', 80, 80)->size(100);
    $this->post(route('admin.profile.images.store'), ['images' => [$second]])->assertRedirect();
    $other = UserImage::query()->where('user_id', $actor->id)->whereKeyNot($image->id)->firstOrFail();

    $this->post(route('admin.profile.images.mark-featured', $other))->assertRedirect();
    expect($other->fresh()->featured)->toBeTrue()
        ->and($image->fresh()->featured)->toBeFalse();

    $this->patch(route('admin.profile.images.public', $other), ['public' => true])->assertRedirect();
    expect($other->fresh()->public)->toBeTrue()
        ->and($other->fresh()->isPubliclyVisible($actor->fresh()))->toBeTrue();

    $actor->update(['is_public' => false]);
    expect($other->fresh()->isPubliclyVisible($actor->fresh()))->toBeFalse();

    $this->get(route('admin.profile.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Users/Edit')
            ->has('images', 2)
            ->where('avatar_url', $other->fresh()->url()));

    $path = $other->image;
    $this->delete(route('admin.profile.images.destroy', $other))->assertRedirect();
    expect(UserImage::query()->whereKey($other->id)->exists())->toBeFalse()
        ->and(UserImage::withTrashed()->whereKey($other->id)->exists())->toBeTrue()
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});

it('rechaza formato peso y limite 20', function () {
    $actor = actingImageManager($this->company);

    $this->post(route('admin.profile.images.store'), [
        'images' => [UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')],
    ])->assertInvalid(['images.0']);

    $this->post(route('admin.profile.images.store'), [
        'images' => [UploadedFile::fake()->image('big.jpg')->size(2048)],
    ])->assertInvalid(['images.0']);

    for ($i = 0; $i < 20; $i++) {
        UserImage::factory()->create([
            'user_id' => $actor->id,
            'image' => "users/{$actor->id}/images/seed-{$i}.jpg",
        ]);
    }

    $this->post(route('admin.profile.images.store'), [
        'images' => [UploadedFile::fake()->image('overflow.jpg')->size(100)],
    ])->assertInvalid(['images']);
});

it('company-users: mutaciones con update; 403 sin update; perímetro SA', function () {
    $actor = actingImageManager($this->company);
    $member = User::factory()->create(['email' => 'member-img@example.test']);
    $this->company->users()->attach($member->id);

    $this->post(route('admin.company-users.images.store', $member), [
        'images' => [UploadedFile::fake()->image('m.jpg')->size(120)],
    ])->assertRedirect();

    $memberImage = UserImage::query()->where('user_id', $member->id)->firstOrFail();

    $this->post(route('admin.company-users.images.mark-featured', [$member, $memberImage]))
        ->assertRedirect();
    $this->patch(route('admin.company-users.images.public', [$member, $memberImage]), [
        'public' => true,
    ])->assertRedirect();

    $noUpdate = actingImageManager($this->company, withUpdate: false);
    $this->actingAs($noUpdate);
    session(['current_company_id' => $this->company->id]);
    withCurrentCompany($this->company);

    $this->post(route('admin.company-users.images.store', $member), [
        'images' => [UploadedFile::fake()->image('denied.jpg')->size(50)],
    ])->assertForbidden();
    $this->delete(route('admin.company-users.images.destroy', [$member, $memberImage]))
        ->assertForbidden();

    $sa = User::factory()->create();
    actingWithCompany($sa, $this->company);
    setPermissionsTeamId(null);
    $sa->assignRole(Role::SUPER_ADMIN);
    $sa->unsetRelation('roles');

    $foreign = User::factory()->create(['email' => 'foreign-img@example.test']);
    $this->other->users()->attach($foreign->id);
    $foreignImage = UserImage::factory()->create([
        'user_id' => $foreign->id,
        'image' => "users/{$foreign->id}/images/foreign.jpg",
    ]);
    Storage::disk('public')->put($foreignImage->image, 'x');

    $this->post(route('admin.company-users.images.store', $foreign), [
        'images' => [UploadedFile::fake()->image('sa.jpg')->size(50)],
    ])->assertForbidden();
    $this->delete(route('admin.company-users.images.destroy', [$foreign, $foreignImage]))
        ->assertForbidden();

    expect(UserImage::query()->where('user_id', $foreign->id)->count())->toBe(1);
});

it('al borrar featured libera hueco y permite marcar otra', function () {
    $actor = actingImageManager($this->company);

    $this->post(route('admin.profile.images.store'), [
        'images' => [
            UploadedFile::fake()->image('a.jpg', 80, 80)->size(80),
            UploadedFile::fake()->image('b.jpg', 80, 80)->size(80),
        ],
    ])->assertRedirect();

    $images = UserImage::query()->where('user_id', $actor->id)->orderBy('id')->get();
    expect($images)->toHaveCount(2);

    $featured = $images[0];
    $remaining = $images[1];

    $this->post(route('admin.profile.images.mark-featured', $featured))->assertRedirect();
    expect($featured->fresh()->featured)->toBeTrue();

    $this->delete(route('admin.profile.images.destroy', $featured))->assertRedirect();

    $trashed = UserImage::withTrashed()->whereKey($featured->id)->firstOrFail();
    expect($trashed->trashed())->toBeTrue()
        ->and($trashed->featured)->toBeFalse()
        ->and(UserImage::query()->where('user_id', $actor->id)->where('featured', true)->exists())->toBeFalse();

    $this->post(route('admin.profile.images.mark-featured', $remaining))->assertRedirect();

    expect($remaining->fresh()->featured)->toBeTrue()
        ->and(UserImage::query()->where('user_id', $actor->id)->where('featured', true)->count())->toBe(1)
        ->and(UserImage::withTrashed()->where('user_id', $actor->id)->where('featured', true)->count())->toBe(1);
});

it('UI Dropzone kit y tab imagenes sin placeholder', function () {
    $dropzone = file_get_contents(resource_path('js/Components/Form/Dropzone.tsx'));
    $panel = file_get_contents(resource_path('js/Pages/Users/components/UserImagesPanel.tsx'));
    $edit = file_get_contents(resource_path('js/Pages/Users/Edit.tsx'));
    $index = file_get_contents(resource_path('js/Components/Form/index.ts'));
    $menu = file_get_contents(resource_path('js/Components/Topbar/UserMenu.tsx'));

    expect($dropzone)->toContain('useDropzone')
        ->and($index)->toContain('Dropzone')
        ->and($panel)->toContain('Dropzone')
        ->and($panel)->toContain('markFeaturedUrl')
        ->and($edit)->toContain("id: 'images'")
        ->and($edit)->toContain('UserImagesPanel')
        ->and($menu)->toContain('avatarUrl');
});
