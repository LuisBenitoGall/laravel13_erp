<?php

use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use App\Domains\Admin\Models\Role;
use App\Domains\Partners\Models\Company;
use App\Models\User;
use App\Support\Table\TableRequest;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);
    $this->seed(AdminCatalogSeeder::class);

    $this->user = User::query()->where('email', config('superadmin.email'))->firstOrFail();
    $this->company = Company::query()->where('slug', 'acme')->firstOrFail();
    actingWithCompany($this->user, $this->company);
});

it('acepta filtros whitelist name/status/level y ignora campos no whitelist', function () {
    Module::factory()->create([
        'name' => 'Alpha Mod',
        'slug' => 'alpha-mod',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);
    Module::factory()->create([
        'name' => 'Beta Mod',
        'slug' => 'beta-mod',
        'level' => ModuleLevel::Optional,
        'status' => ModuleStatus::Inactive,
    ]);

    $this->getJson(route('admin.modules.table', [
        'filter' => json_encode([
            ['field' => 'name', 'type' => 'like', 'value' => 'Alpha'],
            ['field' => 'hacked', 'type' => '=', 'value' => 'x'],
            ['field' => 'slug', 'type' => '=', 'value' => 'ignored'],
        ]),
    ]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'Alpha Mod');

    $this->getJson(route('admin.modules.table', [
        'filter' => json_encode([
            ['field' => 'status', 'type' => '=', 'value' => 'inactive'],
        ]),
    ]))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Beta Mod');
});

it('export xlsx respeta filtro y Policy viewAny', function () {
    Module::factory()->create([
        'name' => 'Exportable',
        'slug' => 'exportable-mod',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);

    $this->get(route('admin.modules.table', [
        'export' => 'xlsx',
        'filter' => json_encode([
            ['field' => 'name', 'type' => 'like', 'value' => 'Exportable'],
        ]),
    ]))
        ->assertOk()
        ->assertHeader('content-disposition');

    $guest = User::factory()->create();
    actingWithCompany($guest, $this->company);

    $this->get(route('admin.modules.table', ['export' => 'xlsx']))
        ->assertForbidden();
});

it('export pdf respeta filtro y Policy viewAny', function () {
    Module::factory()->create([
        'name' => 'Pdfable',
        'slug' => 'pdfable-mod',
        'level' => ModuleLevel::Basic,
        'status' => ModuleStatus::Active,
    ]);

    $this->get(route('admin.modules.table', [
        'export' => 'pdf',
        'filter' => json_encode([
            ['field' => 'name', 'type' => 'like', 'value' => 'Pdfable'],
        ]),
    ]))
        ->assertOk()
        ->assertHeader('content-disposition');
});

it('Index Modules declara ServerTable de referencia sin advanced search', function () {
    $source = (string) file_get_contents(resource_path('js/Pages/Admin/Modules/Index.tsx'));

    expect($source)->toContain('persistKey="admin.modules"')
        ->and($source)->toContain('exportable')
        ->and($source)->toContain('rowActions')
        ->and($source)->toContain("kind: 'toggle'")
        ->and($source)->toContain('patchJson')
        ->and($source)->toContain('/admin/modules/${row.id}/status')
        ->and($source)->toContain("labelKey: 'editar'")
        ->and($source)->toContain("field: 'name'")
        ->and($source)->toContain("field: 'color'")
        ->and($source)->toContain("field: 'icon'")
        ->and($source)->toContain("field: 'level'")
        ->and($source)->toContain("field: 'created_at'")
        ->and($source)->toContain("field: 'status'")
        ->and($source)->not->toContain("field: 'slug'")
        ->and($source)->not->toContain('busqueda_avanzada')
        ->and($source)->not->toContain('AdvancedSearchModal')
        ->and($source)->toContain('showBack={false}');
});

it('toggle status de módulo persiste por PATCH JSON sin redirect', function () {
    $module = Module::factory()->create([
        'name' => 'Toggleable',
        'slug' => 'toggleable-mod',
        'status' => ModuleStatus::Active,
    ]);

    $this->patchJson(route('admin.modules.status', $module), ['status' => 'inactive'])
        ->assertOk()
        ->assertJsonPath('status', 'inactive');

    expect($module->fresh()->status)->toBe(ModuleStatus::Inactive);

    $this->patchJson(route('admin.modules.status', $module), ['status' => 'active'])
        ->assertOk()
        ->assertJsonPath('status', 'active');

    expect($module->fresh()->status)->toBe(ModuleStatus::Active);
});

it('toggle status exige permiso modules.update', function () {
    Role::findOrCreate(Role::GUEST);
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $module = Module::factory()->create([
        'name' => 'Forbidden Toggle',
        'slug' => 'forbidden-toggle',
        'status' => ModuleStatus::Active,
    ]);

    $this->patchJson(route('admin.modules.status', $module), ['status' => 'inactive'])
        ->assertForbidden();
});

it('no permite desactivar módulos shell admin/workspace', function () {
    $admin = Module::query()->where('slug', 'admin')->firstOrFail();

    $this->patchJson(route('admin.modules.status', $admin), ['status' => 'inactive'])
        ->assertRedirect();

    expect($admin->fresh()->status)->toBe(ModuleStatus::Active);

    $workspace = Module::query()->where('slug', 'workspace')->first();

    if ($workspace !== null) {
        $this->patchJson(route('admin.modules.status', $workspace), ['status' => 'inactive'])
            ->assertRedirect();

        expect($workspace->fresh()->status)->toBe(ModuleStatus::Active);
    }
});

it('ningún Index ServerTable muestra columna slug', function () {
    $files = [
        resource_path('js/Pages/Admin/Modules/Index.tsx'),
        resource_path('js/Pages/Admin/Functionalities/Index.tsx'),
        resource_path('js/Pages/Partners/Companies/Index.tsx'),
    ];

    foreach ($files as $file) {
        $source = (string) file_get_contents($file);
        expect($source)->not->toContain("field: 'slug'")
            ->and($source)->not->toContain('field: "slug"')
            ->and($source)->not->toContain("field: 'module.slug'");
    }
});

it('Acciones i18n capitalizada y CSS de borde en header filters', function () {
    $es = json_decode((string) file_get_contents(lang_path('es.json')), true);
    $css = (string) file_get_contents(resource_path('css/tabulator-erp.css'));

    expect($es['acciones'])->toBe('Acciones')
        ->and($css)->toContain('.tabulator-header-filter input')
        ->and($css)->toContain('--st-hf-height')
        ->and($css)->toContain('--st-hf-border: 1px solid #cbd5e1')
        ->and($css)->toContain('.tabulator-date-range-trigger')
        ->and($css)->toContain('.tabulator-actions-btn')
        ->and($css)->toContain('background: #0f766e')
        ->and($css)->toContain('.tabulator-actions-btn-danger')
        ->and($css)->toContain('background: #fee2e2')
        ->and($css)->toContain('.tabulator-actions-toggle')
        ->and($css)->toContain('vertical-align: middle')
        ->and($css)->toContain('.cell-vcenter');
});

it('lib/format define DATE_FORMATS por locale', function () {
    $source = (string) file_get_contents(resource_path('js/lib/format.ts'));

    expect($source)->toContain("es: 'dd/mm/yyyy'")
        ->and($source)->toContain("ca: 'dd/mm/yyyy'")
        ->and($source)->toContain("en: 'yyyy/mm/dd'")
        ->and($source)->toContain('export function formatDate');
});

it('filtra created_at por día concreto con date_between (datetime)', function () {
    $inRange = Module::factory()->create([
        'name' => 'Dia Exacto',
        'slug' => 'dia-exacto',
        'created_at' => '2026-03-15 14:30:00',
    ]);
    Module::factory()->create([
        'name' => 'Otro Dia',
        'slug' => 'otro-dia',
        'created_at' => '2026-03-16 09:00:00',
    ]);

    $response = $this->getJson(route('admin.modules.table', [
        'filter' => json_encode([
            [
                'field' => 'created_at',
                'type' => 'date_between',
                'value' => ['from' => '2026-03-15', 'to' => '2026-03-15'],
            ],
        ]),
    ]));

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');
    expect($names)->toContain('Dia Exacto')
        ->and($names)->not->toContain('Otro Dia')
        ->and($inRange->created_at->toDateString())->toBe('2026-03-15');
});

it('filtra created_at por intervalo y extremos abiertos', function () {
    Module::factory()->create([
        'name' => 'Antes',
        'slug' => 'antes-mod',
        'created_at' => '2026-01-01 10:00:00',
    ]);
    Module::factory()->create([
        'name' => 'Medio',
        'slug' => 'medio-mod',
        'created_at' => '2026-02-15 12:00:00',
    ]);
    Module::factory()->create([
        'name' => 'Despues',
        'slug' => 'despues-mod',
        'created_at' => '2026-03-20 08:00:00',
    ]);

    $both = $this->getJson(route('admin.modules.table', [
        'filter' => json_encode([
            [
                'field' => 'created_at',
                'type' => 'date_between',
                'value' => ['from' => '2026-02-01', 'to' => '2026-02-28'],
            ],
        ]),
    ]));
    $both->assertOk();
    expect(collect($both->json('data'))->pluck('name')->all())->toContain('Medio')
        ->and(collect($both->json('data'))->pluck('name')->all())->not->toContain('Antes')
        ->and(collect($both->json('data'))->pluck('name')->all())->not->toContain('Despues');

    $fromOnly = $this->getJson(route('admin.modules.table', [
        'filter' => json_encode([
            [
                'field' => 'created_at',
                'type' => 'date_between',
                'value' => ['from' => '2026-03-01', 'to' => null],
            ],
        ]),
    ]));
    $fromOnly->assertOk();
    expect(collect($fromOnly->json('data'))->pluck('name')->all())->toContain('Despues')
        ->and(collect($fromOnly->json('data'))->pluck('name')->all())->not->toContain('Antes')
        ->and(collect($fromOnly->json('data'))->pluck('name')->all())->not->toContain('Medio');

    $toOnly = $this->getJson(route('admin.modules.table', [
        'filter' => json_encode([
            [
                'field' => 'created_at',
                'type' => 'date_between',
                'value' => ['from' => null, 'to' => '2026-01-31'],
            ],
        ]),
    ]));
    $toOnly->assertOk();
    expect(collect($toOnly->json('data'))->pluck('name')->all())->toContain('Antes')
        ->and(collect($toOnly->json('data'))->pluck('name')->all())->not->toContain('Medio')
        ->and(collect($toOnly->json('data'))->pluck('name')->all())->not->toContain('Despues');
});

it('ignora date_between malformado y tipo no whitelist (sin aplicar filtro)', function () {
    Module::factory()->create([
        'name' => 'Solo Marzo',
        'slug' => 'solo-marzo',
        'created_at' => '2026-03-15 12:00:00',
    ]);
    Module::factory()->create([
        'name' => 'Solo Abril',
        'slug' => 'solo-abril',
        'created_at' => '2026-04-15 12:00:00',
    ]);

    // Rutas web no devuelven 422 JSON (shouldRenderJsonWhen = api/*); whitelist en filters().
    $malformed = $this->getJson(route('admin.modules.table', [
        'filter' => json_encode([
            [
                'field' => 'created_at',
                'type' => 'date_between',
                'value' => ['from' => '15/03/2026', 'to' => null],
            ],
        ]),
    ]));
    $malformed->assertOk();
    expect(collect($malformed->json('data'))->pluck('name')->all())
        ->toContain('Solo Marzo')
        ->toContain('Solo Abril');

    $badType = $this->getJson(route('admin.modules.table', [
        'filter' => json_encode([
            [
                'field' => 'created_at',
                'type' => 'hack_range',
                'value' => ['from' => '2026-03-15', 'to' => '2026-03-15'],
            ],
        ]),
    ]));
    $badType->assertOk();
    expect(collect($badType->json('data'))->pluck('name')->all())
        ->toContain('Solo Marzo')
        ->toContain('Solo Abril');
});

it('ServerTable declara dateRangeHeaderFilter para columnas date', function () {
    $source = (string) file_get_contents(resource_path('js/Components/Table/ServerTable.tsx'));
    $filter = (string) file_get_contents(resource_path('js/Components/Table/dateRangeHeaderFilter.ts'));
    $css = (string) file_get_contents(resource_path('css/tabulator-erp.css'));

    expect($source)->toContain('createDateRangeHeaderFilter')
        ->and($source)->toContain('date_between')
        ->and($filter)->toContain('tabulator-date-range-trigger')
        ->and($filter)->toContain('tabulator-date-range-panel')
        ->and($filter)->toContain('params.fromLabel')
        ->and($filter)->toContain('params.toLabel')
        ->and($filter)->toContain('summarizeRange')
        ->and($filter)->toContain("fromInput.type = 'date'")
        ->and($filter)->toContain("toInput.type = 'date'")
        ->and($filter)->toContain('wrap.appendChild(trigger)')
        ->and($filter)->not->toContain('wrap.appendChild(fromInput)')
        ->and($css)->toContain('.tabulator-date-range-trigger')
        ->and($css)->toContain('.tabulator-date-range-panel');
});

it('documenta cap de export', function () {
    expect(TableRequest::EXPORT_MAX_ROWS)->toBe(5000);
});

it('rechaza export a no Super Admin sin permiso modules.viewAny', function () {
    Role::findOrCreate(Role::GUEST);
    $user = User::factory()->create();
    actingWithCompany($user, $this->company);

    $this->get(route('admin.modules.table', ['export' => 'pdf']))
        ->assertForbidden();
});
