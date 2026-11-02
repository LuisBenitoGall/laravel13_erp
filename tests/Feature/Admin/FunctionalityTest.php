<?php

use App\Domains\Admin\Actions\CreateFunctionality;
use App\Domains\Admin\Actions\UpdateFunctionality;
use App\Domains\Admin\Enums\FunctionalityStatus;
use App\Domains\Admin\Enums\ModuleLevel;
use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    setPermissionsTeamId(null);

    $this->company = Company::factory()->create();
    Module::factory()->create([
        'slug' => 'admin',
        'level' => ModuleLevel::AdminOnly,
        'status' => ModuleStatus::Active,
    ]);
    enableModuleForCompany($this->company, 'admin');
});

it('crea Functionality y provisiona 5 permisos + comodín', function () {
    $module = Module::query()->where('slug', 'admin')->firstOrFail();

    $functionality = app(CreateFunctionality::class)->handle([
        'module_id' => $module->id,
        'name' => 'Modules',
        'slug' => 'modules',
        'label' => ['es' => 'Módulos', 'en' => 'Modules'],
        'status' => FunctionalityStatus::Active->value,
    ]);

    foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
        expect(Permission::query()->where('name', "modules.{$ability}")->exists())->toBeTrue();
    }

    expect(Permission::query()->where('name', 'admin.access')->exists())->toBeTrue()
        ->and($functionality->slug)->toBe('modules');
});

it('rechaza cambio de slug en UpdateFunctionality', function () {
    $module = Module::factory()->create(['slug' => 'other']);
    $functionality = Functionality::factory()->create(['module_id' => $module->id, 'slug' => 'fixed']);

    expect(fn () => app(UpdateFunctionality::class)->handle($functionality, [
        'module_id' => $module->id,
        'name' => 'Fixed',
        'slug' => 'changed',
        'label' => ['es' => 'X'],
        'status' => FunctionalityStatus::Active->value,
    ]))->toThrow(InvalidArgumentException::class);
});

it('Functionality es visible globalmente', function () {
    Functionality::factory()->create(['slug' => 'shared-fn']);

    expect(Functionality::query()->where('slug', 'shared-fn')->count())->toBe(1);
});

it('no expone CRUD genérico de Functionalities', function () {
    expect(Route::has('admin.functionalities.index'))->toBeFalse()
        ->and(Route::has('admin.functionalities.create'))->toBeFalse()
        ->and(Route::has('admin.functionalities.edit'))->toBeFalse()
        ->and(Route::has('admin.functionalities.table'))->toBeFalse()
        ->and(Route::has('admin.functionalities.store'))->toBeFalse()
        ->and(Route::has('admin.modules.functionalities.table'))->toBeTrue()
        ->and(Route::has('admin.modules.functionalities.store'))->toBeTrue()
        ->and(Route::has('admin.functionalities.update'))->toBeTrue()
        ->and(Route::has('admin.functionalities.status'))->toBeTrue()
        ->and(Route::has('admin.functionalities.destroy'))->toBeTrue();

    $this->get('/admin/functionalities')->assertNotFound();
});
