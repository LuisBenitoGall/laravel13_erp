<?php

use App\Domains\Admin\Actions\AuthorizeModuleForCompany;
use App\Domains\Admin\Actions\EnableModuleForCompany;
use App\Domains\Admin\Models\Module;
use App\Domains\Partners\Models\Company;
use App\Models\User;
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

/**
 * Autentica usuario con empresa de sesión (pertenencia + CurrentCompany).
 */
function actingWithCompany(User $user, Company $company): void
{
    if (! $user->companies()->where('companies.id', $company->id)->exists()) {
        $user->companies()->attach($company->id);
    }

    test()->actingAs($user);
    session(['current_company_id' => $company->id]);
    withCurrentCompany($company);
}

/**
 * Autoriza y enciende un módulo (por slug) para la company.
 */
function enableModuleForCompany(Company $company, string $moduleSlug = 'admin'): Module
{
    $module = Module::query()->where('slug', $moduleSlug)->firstOrFail();
    app(AuthorizeModuleForCompany::class)->handle($company, $module);
    app(EnableModuleForCompany::class)->handle($company, $module);

    return $module;
}
