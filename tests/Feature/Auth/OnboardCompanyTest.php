<?php

use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    Storage::fake('public');
});

it('crea company Active como manager y entra al ERP', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.company.store'), [
            'nif' => 'B11111111',
            'name' => 'Onboard Co',
            'tradename' => 'Onboard',
        ])
        ->assertRedirect();

    $company = Company::query()->where('nif', 'B11111111')->firstOrFail();

    expect($company->status->value)->toBe('active')
        ->and($user->companies()->where('companies.id', $company->id)->exists())->toBeTrue()
        ->and(session('current_company_id'))->toBe($company->id);

    setPermissionsTeamId($company->id);
    expect($user->fresh()->hasRole('manager'))->toBeTrue();
});

it('rechaza nif duplicado', function () {
    Company::factory()->create(['nif' => 'B22222222']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.company.store'), [
            'nif' => 'B22222222',
            'name' => 'Dup Co',
        ])
        ->assertSessionHasErrors('nif');
});

it('rechaza logo con formato inválido', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.company.store'), [
            'nif' => 'B33333333',
            'name' => 'Logo Co',
            'logo' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('logo');
});

it('permite alta sin logo', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.company.store'), [
            'nif' => 'B44444444',
            'tradename' => 'Solo comercial',
        ])
        ->assertRedirect();

    expect(Company::query()->where('nif', 'B44444444')->value('logo'))->toBeNull();
});

it('login sin company redirige a onboarding autenticado', function () {
    $user = User::factory()->create([
        'email' => 'lonely@example.com',
        'password' => 'password',
    ]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('onboarding.company'));

    $this->assertAuthenticatedAs($user);
});
