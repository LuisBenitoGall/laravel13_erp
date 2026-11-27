<?php

use App\Domains\Admin\Models\Company;
use App\Domains\Catalog\Enums\CategoryStatus;
use App\Domains\Catalog\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('detecta ciclos y enumera descendientes', function () {
    $company = Company::factory()->create();
    $root = Category::factory()->create(['company_id' => $company->id, 'name' => 'Root']);
    $child = Category::factory()->childOf($root)->create(['name' => 'Child']);
    $grand = Category::factory()->childOf($child)->create(['name' => 'Grand']);

    expect($root->wouldCycleWithParent($root))->toBeTrue()
        ->and($root->wouldCycleWithParent($child))->toBeTrue()
        ->and($root->wouldCycleWithParent($grand))->toBeTrue()
        ->and($child->wouldCycleWithParent($grand))->toBeTrue()
        ->and($grand->wouldCycleWithParent($root))->toBeFalse()
        ->and($root->descendantIds()->all())->toEqualCanonicalizing([$child->id, $grand->id])
        ->and($child->isDescendantOf($root))->toBeTrue()
        ->and($root->isDescendantOf($child))->toBeFalse();
});

it('factory states active/inactive y childOf', function () {
    $company = Company::factory()->create();
    $parent = Category::factory()->for($company)->active()->create();
    $child = Category::factory()->childOf($parent)->inactive()->create();

    expect($parent->status)->toBe(CategoryStatus::Active)
        ->and($child->parent_id)->toBe($parent->id)
        ->and($child->company_id)->toBe($company->id)
        ->and($child->status)->toBe(CategoryStatus::Inactive);
});
