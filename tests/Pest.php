<?php

use App\Support\Tenancy\CurrentCompany;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature', 'Architecture', 'Unit', 'Browser');

/**
 * Fija el tenant activo del test (CurrentCompany singleton).
 */
function withCurrentCompany(int|object $company): void
{
    $id = is_object($company)
        ? (int) ($company->id ?? $company->company_id ?? 0)
        : $company;

    app(CurrentCompany::class)->set($id);
}
