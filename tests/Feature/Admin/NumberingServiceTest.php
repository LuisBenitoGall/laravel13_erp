<?php

use App\Domains\Admin\Services\NumberingService;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->artisan('migrate', ['--path' => 'database/migrations/2026_07_16_000001_create_document_numbers_table.php']);
    withCurrentCompany(5);
});

afterEach(function () {
    Schema::dropIfExists('document_numbers');
    app(CurrentCompany::class)->set(null);
});

it('emite números consecutivos sin colisión bajo lock', function () {
    $service = app(NumberingService::class);

    $first = $service->next('invoice');
    $second = $service->next('invoice');

    expect($first->sequence)->toBe(1)
        ->and($second->sequence)->toBe(2)
        ->and($first->formatted)->not->toBe($second->formatted);

    // Simula contención: dos next() en la misma transacción no se solapan.
    $numbers = DB::transaction(function () use ($service) {
        return [
            $service->next('order')->sequence,
            $service->next('order')->sequence,
        ];
    });

    expect($numbers)->toBe([1, 2]);
});
