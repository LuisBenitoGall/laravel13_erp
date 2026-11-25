<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('migra workplaces con dueño = Delegación a la matriz + pivote', function () {
    $matrix = Company::factory()->create();
    $delegation = Company::factory()->create(['parent_id' => $matrix->id]);
    $matrixFeatured = Workplace::factory()->featured()->create([
        'company_id' => $matrix->id,
        'name' => 'Matriz HQ',
    ]);
    $childOwned = Workplace::factory()->featured()->create([
        'company_id' => $delegation->id,
        'name' => 'Antigua hija',
    ]);

    // RefreshDatabase ya aplicó la migración; re-simular estado pre-opción-A
    // creando filas hijas y re-ejecutando la lógica vía migrate:refresh no aplica.
    // Forzamos company_id hija y borramos pivote; luego re-corremos el up() de la migration.
    DB::table('workplaces')->where('id', $childOwned->id)->update([
        'company_id' => $delegation->id,
        'featured' => true,
    ]);
    DB::table('workplace_delegation')->where('workplace_id', $childOwned->id)->delete();

    $migration = require database_path('migrations/2026_11_25_063717_migrate_delegation_owned_workplaces_to_matrix.php');
    $migration->up();

    $fresh = $childOwned->fresh();
    expect($fresh->company_id)->toBe($matrix->id)
        // Matriz ya tenía featured: el migrado pierde featured.
        ->and($fresh->featured)->toBeFalse()
        ->and($matrixFeatured->fresh()->featured)->toBeTrue();

    expect(DB::table('workplace_delegation')
        ->where('workplace_id', $childOwned->id)
        ->where('company_id', $delegation->id)
        ->exists())->toBeTrue();

    $orphanOwners = Workplace::query()
        ->withoutGlobalScope('company')
        ->whereNull('deleted_at')
        ->whereIn('company_id', Company::query()->whereNotNull('parent_id')->pluck('id'))
        ->count();

    expect($orphanOwners)->toBe(0);
});

it('artisan migrate deja cero workplaces activos con dueño delegación', function () {
    // Tras RefreshDatabase, no deberían quedar dueños-hija.
    $count = Workplace::query()
        ->withoutGlobalScope('company')
        ->whereNull('deleted_at')
        ->whereIn('company_id', Company::query()->whereNotNull('parent_id')->select('id'))
        ->count();

    expect($count)->toBe(0);
});
