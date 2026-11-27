<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('elegible fuera de Edit solo si public y Product Active', function () {
    $company = Company::factory()->create();
    $active = Product::factory()->active()->create(['company_id' => $company->id]);
    $inactive = Product::factory()->inactive()->create(['company_id' => $company->id]);

    $eligible = ProductImage::factory()->forProduct($active)->publiclyVisible()->create();
    $privateOnActive = ProductImage::factory()->forProduct($active)->create(['public' => false]);
    $publicOnInactive = ProductImage::factory()->forProduct($inactive)->publiclyVisible()->create();

    expect($eligible->isEligibleOutsideEdit())->toBeTrue()
        ->and($privateOnActive->isEligibleOutsideEdit())->toBeFalse()
        ->and($publicOnInactive->isEligibleOutsideEdit())->toBeFalse();
});
