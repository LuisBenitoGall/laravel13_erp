<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('crea productos en la company dada con states physical/service/active', function () {
    $company = Company::factory()->create();

    $physical = Product::factory()->for($company)->physical()->create();
    $service = Product::factory()->for($company)->service()->active()->create();

    expect($physical->company_id)->toBe($company->id)
        ->and($physical->type)->toBe(ProductType::Physical)
        ->and($physical->status)->toBe(ProductStatus::Inactive)
        ->and($service->company_id)->toBe($company->id)
        ->and($service->type)->toBe(ProductType::Service)
        ->and($service->status)->toBe(ProductStatus::Active);
});
