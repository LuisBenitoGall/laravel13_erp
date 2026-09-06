<?php

use App\Support\Table\TableBuilder;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\TenantProbe;
use Tests\Fixtures\TenantProbeTableRequest;

beforeEach(function () {
    Schema::create('tenant_probes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('company_id');
        $table->string('name');
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });

    withCurrentCompany(1);

    TenantProbe::query()->create(['name' => 'Alpha']);
    TenantProbe::query()->create(['name' => 'Beta']);
    TenantProbe::query()->create(['name' => 'Gamma']);
});

afterEach(function () {
    Schema::dropIfExists('tenant_probes');
    app(CurrentCompany::class)->set(null);
});

it('aplica whitelist de sort y paginación', function () {
    $request = TenantProbeTableRequest::create('/table', 'GET', [
        'page' => 1,
        'size' => 2,
        'sort' => [['field' => 'name', 'dir' => 'desc']],
        'filter' => [['field' => 'hacked', 'type' => '=', 'value' => 'x']],
    ]);
    $request->setContainer(app())->setRedirector(app('redirect'));
    $request->validateResolved();

    $response = app(TableBuilder::class)
        ->for(TenantProbe::query())
        ->searchable(['name'])
        ->request($request)
        ->json();

    $payload = $response->getData(true);

    expect($payload['total'])->toBe(3)
        ->and($payload['last_page'])->toBe(2)
        ->and(count($payload['data']))->toBe(2)
        ->and($payload['data'][0]['name'])->toBe('Gamma');
});

it('filtra por search whitelist', function () {
    $request = TenantProbeTableRequest::create('/table', 'GET', [
        'page' => 1,
        'size' => 25,
        'filter' => [['field' => 'search', 'type' => 'like', 'value' => 'Bet']],
    ]);
    $request->setContainer(app())->setRedirector(app('redirect'));
    $request->validateResolved();

    $payload = app(TableBuilder::class)
        ->for(TenantProbe::query())
        ->searchable(['name'])
        ->request($request)
        ->json()
        ->getData(true);

    expect($payload['total'])->toBe(1)
        ->and($payload['data'][0]['name'])->toBe('Beta');
});
