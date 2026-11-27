<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\FamilyStatus;
use App\Domains\Catalog\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('factory crea Family con company y states active/inactive', function () {
    $company = Company::factory()->create();

    $inactive = Family::factory()->for($company)->inactive()->create();
    $active = Family::factory()->for($company)->active()->create();

    expect($inactive->company_id)->toBe($company->id)
        ->and($inactive->status)->toBe(FamilyStatus::Inactive)
        ->and($inactive->slug)->not->toBe('')
        ->and($active->status)->toBe(FamilyStatus::Active);
});
