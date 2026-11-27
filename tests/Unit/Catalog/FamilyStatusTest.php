<?php

use App\Domains\Catalog\Enums\FamilyStatus;

it('permite transiciones Active ↔ Inactive', function () {
    expect(FamilyStatus::Active->canTransitionTo(FamilyStatus::Inactive))->toBeTrue()
        ->and(FamilyStatus::Inactive->canTransitionTo(FamilyStatus::Active))->toBeTrue()
        ->and(FamilyStatus::Active->canTransitionTo(FamilyStatus::Active))->toBeTrue()
        ->and(FamilyStatus::Inactive->canTransitionTo(FamilyStatus::Inactive))->toBeTrue();
});
