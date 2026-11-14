<?php

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
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

function actingHierarchyEditor(Company ...$companies): User
{
    $session = $companies[0];
    enableModuleForCompany($session, 'empresas');

    $user = User::factory()->create(['status' => true]);
    actingWithCompany($user, $session);

    foreach ($companies as $company) {
        if (! $user->companies()->where('companies.id', $company->id)->exists()) {
            $user->companies()->attach($company->id);
        }
    }

    setPermissionsTeamId($session->id);
    $user->givePermissionTo([
        Permission::findOrCreate('empresas.access'),
        Permission::findOrCreate('companies.view'),
        Permission::findOrCreate('companies.update'),
        Permission::findOrCreate('companies.create'),
    ]);
    $user->unsetRelation('roles');
    $user->unsetRelation('permissions');

    return $user;
}

it('asigna y quita padre; permite tres niveles', function () {
    $a = Company::factory()->create(['name' => 'A Holding', 'status' => CompanyStatus::Active]);
    $b = Company::factory()->create(['name' => 'B Mid', 'status' => CompanyStatus::Active]);
    $c = Company::factory()->create(['name' => 'C Leaf', 'status' => CompanyStatus::Active]);
    actingHierarchyEditor($a, $b, $c);

    $this->put(route('admin.companies.parent', $b), [
        'parent_id' => $a->id,
    ])->assertRedirect();

    $this->put(route('admin.companies.parent', $c), [
        'parent_id' => $b->id,
    ])->assertRedirect();

    expect($b->fresh()->parent_id)->toBe($a->id)
        ->and($c->fresh()->parent_id)->toBe($b->id)
        ->and($c->fresh()->isDescendantOf($a))->toBeTrue();

    $this->put(route('admin.companies.parent', $c), [
        'parent_id' => null,
    ])->assertRedirect();

    expect($c->fresh()->parent_id)->toBeNull();
});

it('rechaza ciclo self y descendiente', function () {
    $a = Company::factory()->create(['status' => CompanyStatus::Active]);
    $b = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingHierarchyEditor($a, $b);

    $this->put(route('admin.companies.parent', $b), ['parent_id' => $a->id])->assertRedirect();

    $this->from(route('admin.companies.edit', ['company' => $a, 'tab' => 'hierarchy']))
        ->put(route('admin.companies.parent', $a), ['parent_id' => $a->id])
        ->assertSessionHasErrors('parent_id');

    $this->from(route('admin.companies.edit', ['company' => $a, 'tab' => 'hierarchy']))
        ->put(route('admin.companies.parent', $a), ['parent_id' => $b->id])
        ->assertSessionHasErrors('parent_id');
});

it('rechaza padre fuera de vinculo user_companies', function () {
    $linked = Company::factory()->create(['status' => CompanyStatus::Active]);
    $foreign = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingHierarchyEditor($linked);

    $this->from(route('admin.companies.edit', ['company' => $linked, 'tab' => 'hierarchy']))
        ->put(route('admin.companies.parent', $linked), ['parent_id' => $foreign->id])
        ->assertSessionHasErrors('parent_id');
});

it('alberga y deja de albergar hijos sin borrar', function () {
    $parent = Company::factory()->create(['name' => 'Parent Co', 'status' => CompanyStatus::Active]);
    $child = Company::factory()->create(['name' => 'Child Co', 'status' => CompanyStatus::Active]);
    actingHierarchyEditor($parent, $child);

    $this->post(route('admin.companies.children.attach', $parent), [
        'child_id' => $child->id,
    ])->assertRedirect();

    expect($child->fresh()->parent_id)->toBe($parent->id);

    $this->delete(route('admin.companies.children.detach', [$parent, $child]))
        ->assertRedirect();

    expect($child->fresh()->parent_id)->toBeNull()
        ->and(Company::query()->whereKey($child->id)->exists())->toBeTrue();
});

it('rechaza attach hijo fuera de vinculo o que crea ciclo', function () {
    $a = Company::factory()->create(['name' => 'A', 'status' => CompanyStatus::Active]);
    $b = Company::factory()->create(['name' => 'B', 'status' => CompanyStatus::Active]);
    $foreign = Company::factory()->create(['name' => 'Foreign', 'status' => CompanyStatus::Active]);
    actingHierarchyEditor($a, $b);

    $this->put(route('admin.companies.parent', $b), ['parent_id' => $a->id])->assertRedirect();

    $this->from(route('admin.companies.edit', ['company' => $a, 'tab' => 'hierarchy']))
        ->post(route('admin.companies.children.attach', $a), ['child_id' => $foreign->id])
        ->assertSessionHasErrors('child_id');

    $this->from(route('admin.companies.edit', ['company' => $b, 'tab' => 'hierarchy']))
        ->post(route('admin.companies.children.attach', $b), ['child_id' => $a->id])
        ->assertSessionHasErrors('child_id');
});

it('rechaza detach de company que no es hija con 422', function () {
    $parent = Company::factory()->create(['status' => CompanyStatus::Active]);
    $other = Company::factory()->create(['status' => CompanyStatus::Active]);
    actingHierarchyEditor($parent, $other);

    $this->from(route('admin.companies.edit', ['company' => $parent, 'tab' => 'hierarchy']))
        ->delete(route('admin.companies.children.detach', [$parent, $other]))
        ->assertSessionHasErrors('child');
});

it('Edit expone parent children hierarchy_options y Create no tiene jerarquia', function () {
    $parent = Company::factory()->create(['name' => 'Parent', 'status' => CompanyStatus::Active]);
    $edit = Company::factory()->create(['name' => 'Edit Me', 'status' => CompanyStatus::Active]);
    $other = Company::factory()->create(['name' => 'Other', 'status' => CompanyStatus::Active]);
    actingHierarchyEditor($parent, $edit, $other);

    $edit->update(['parent_id' => $parent->id]);

    $this->get(route('admin.companies.edit', ['company' => $edit, 'tab' => 'hierarchy']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Companies/Edit')
            ->where('parent.id', $parent->id)
            ->has('children')
            ->has('hierarchy_options.parents')
            ->has('hierarchy_options.children')
            ->where('hierarchy_options.parents', fn ($rows) => collect($rows)->contains(
                fn ($row) => (int) $row['id'] === $other->id,
            )));

    $create = file_get_contents(resource_path('js/Pages/Admin/Companies/Create.tsx'));
    $editSrc = file_get_contents(resource_path('js/Pages/Admin/Companies/Edit.tsx'));

    expect($create)->not->toContain('hierarchy')
        ->and($create)->not->toContain('jerarquia')
        ->and($editSrc)->toContain("'hierarchy'")
        ->and($editSrc)->toContain('CompanyHierarchyPanel');
});
