<?php

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\TenantProbe;

beforeEach(function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });

    Schema::create('tenant_probes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('company_id');
        $table->string('name');
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('tenant_probes');
    Schema::dropIfExists('users');
    app(CurrentCompany::class)->set(null);
});

it('aislamiento de tenant: solo ve registros de la empresa activa', function () {
    withCurrentCompany(1);
    TenantProbe::query()->create(['name' => 'A1']);
    TenantProbe::query()->create(['name' => 'A2']);

    withCurrentCompany(2);
    TenantProbe::query()->create(['name' => 'B1']);

    withCurrentCompany(1);
    $names = TenantProbe::query()->pluck('name')->all();

    expect($names)->toBe(['A1', 'A2'])
        ->and(TenantProbe::query()->count())->toBe(2);
});

it('auditoría rellena created_by y updated_by', function () {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Auditor',
        'email' => 'auditor@example.com',
        'password' => bcrypt('secret'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Auth::loginUsingId($userId);
    withCurrentCompany(10);

    $probe = TenantProbe::query()->create(['name' => 'Audited']);

    expect($probe->created_by)->toBe($userId)
        ->and($probe->updated_by)->toBe($userId);

    $probe->update(['name' => 'Audited 2']);
    $probe->refresh();

    expect($probe->updated_by)->toBe($userId);
});
