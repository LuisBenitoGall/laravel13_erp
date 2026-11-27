<?php

use App\Domains\Catalog\Enums\ProductStatus;

it('permite transiciones Active ↔ Inactive y identity', function () {
    expect(ProductStatus::Active->canTransitionTo(ProductStatus::Inactive))->toBeTrue()
        ->and(ProductStatus::Inactive->canTransitionTo(ProductStatus::Active))->toBeTrue()
        ->and(ProductStatus::Active->canTransitionTo(ProductStatus::Active))->toBeTrue()
        ->and(ProductStatus::Inactive->canTransitionTo(ProductStatus::Inactive))->toBeTrue();
});
