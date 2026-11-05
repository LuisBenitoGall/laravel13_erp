<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->company = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'configuracion',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($this->company, 'configuracion');

    $this->user = User::factory()->create();
    Role::findOrCreate('Super Admin');
    $this->user->assignRole('Super Admin');
    actingWithCompany($this->user, $this->company);
});

/** @return array<string, mixed> */
function moduleCreatePayload(array $overrides = []): array
{
    return array_replace_recursive([
        'name' => 'Logistica',
        'label' => ['es' => 'Logística', 'en' => '', 'ca' => ''],
        'explanation' => ['es' => 'Almacén y transporte', 'en' => '', 'ca' => ''],
        'color' => '#112233',
        'icon' => 'truck',
        'level' => ModuleLevel::Optional->value,
        'status' => ModuleStatus::Active->value,
    ], $overrides);
}

it('crea un módulo sin slug de cliente y redirige a Edit', function () {
    $response = $this->post(route('admin.modules.store'), moduleCreatePayload([
        'slug' => 'hacked',
    ]));

    $module = Module::query()->where('slug', 'logistica')->firstOrFail();

    $response->assertRedirect(route('admin.modules.edit', $module));

    expect($module->name)->toBe('Logistica')
        ->and($module->slug)->toBe('logistica')
        ->and($module->icon)->toBe('truck')
        ->and($module->color)->toBe('#112233')
        ->and($module->status)->toBe(ModuleStatus::Active)
        ->and($module->getTranslation('label', 'es'))->toBe('Logística')
        ->and($module->getTranslation('explanation', 'es'))->toBe('Almacén y transporte')
        ->and($module->getTranslation('label', 'en'))->toBe('')
        ->and($module->getTranslation('explanation', 'ca'))->toBe('');
});

it('deja el status en Active cuando el cliente no lo envía', function () {
    $payload = moduleCreatePayload(['name' => 'Sin Estado']);
    unset($payload['status']);

    $this->post(route('admin.modules.store'), $payload)->assertRedirect();

    expect(Module::query()->where('slug', 'sin-estado')->firstOrFail()->status)
        ->toBe(ModuleStatus::Active);
});

it('rechaza un name que colisiona sin distinguir mayúsculas', function () {
    Module::factory()->create([
        'name' => 'Ventas',
        'slug' => 'ventas-legacy',
    ]);

    $this->post(route('admin.modules.store'), moduleCreatePayload([
        'name' => 'ventas',
    ]))->assertSessionHasErrors('name');

    expect(Module::query()->where('slug', 'ventas')->exists())->toBeFalse();
});

it('rechaza un slug derivado que colisiona sin distinguir mayúsculas', function () {
    Module::factory()->create([
        'name' => 'Legacy',
        'slug' => 'almacen',
    ]);

    $this->post(route('admin.modules.store'), moduleCreatePayload([
        'name' => 'Almacen',
    ]))->assertSessionHasErrors('slug');
});

it('rechaza el alta si falta label o explanation del locale principal', function () {
    $this->post(route('admin.modules.store'), moduleCreatePayload([
        'name' => 'Sin Etiqueta',
        'label' => ['es' => '', 'en' => 'Label'],
    ]))->assertSessionHasErrors('label.es');

    $this->post(route('admin.modules.store'), moduleCreatePayload([
        'name' => 'Sin Explicacion',
        'explanation' => ['es' => ''],
    ]))->assertSessionHasErrors('explanation.es');
});

it('acepta locales secundarios vacíos', function () {
    $this->post(route('admin.modules.store'), moduleCreatePayload([
        'name' => 'Solo Principal',
        'label' => ['es' => 'Solo', 'en' => '', 'ca' => ''],
        'explanation' => ['es' => 'Texto', 'en' => '', 'ca' => ''],
    ]))->assertRedirect();

    expect(Module::query()->where('slug', 'solo-principal')->exists())->toBeTrue();
});

it('rechaza un icono fuera del catálogo', function () {
    $this->post(route('admin.modules.store'), moduleCreatePayload([
        'icon' => 'smile',
    ]))->assertSessionHasErrors('icon');
});

it('niega el alta sin permiso modules.create', function () {
    $guest = User::factory()->create();
    actingWithCompany($guest, $this->company);

    $this->post(route('admin.modules.store'), moduleCreatePayload())
        ->assertForbidden();
});

it('Create usa defaultLocale shared y no hardcodea locale principal', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Modules/Create.tsx'));

    expect($source)->toContain('defaultLocale')
        ->and($source)->not->toContain("locale === 'es'")
        ->and($source)->not->toContain('locale === "es"');
});
