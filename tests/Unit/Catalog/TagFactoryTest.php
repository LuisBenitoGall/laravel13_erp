<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\TagStatus;
use App\Domains\Catalog\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('factory crea Tag con company y states active/inactive', function () {
    $company = Company::factory()->create();

    $inactive = Tag::factory()->for($company)->inactive()->create();
    $active = Tag::factory()->for($company)->active()->create();

    expect($inactive->company_id)->toBe($company->id)
        ->and($inactive->status)->toBe(TagStatus::Inactive)
        ->and($inactive->slug)->not->toBe('')
        ->and($active->status)->toBe(TagStatus::Active);
});

it('permite transiciones Active ↔ Inactive', function () {
    expect(TagStatus::Active->canTransitionTo(TagStatus::Inactive))->toBeTrue()
        ->and(TagStatus::Inactive->canTransitionTo(TagStatus::Active))->toBeTrue();
});
