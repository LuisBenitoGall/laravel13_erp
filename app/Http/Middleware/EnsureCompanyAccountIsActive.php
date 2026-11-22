<?php

namespace App\Http\Middleware;

use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Domains\Admin\Models\CompanyAccount;
use App\Support\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate SaaS: company de sesión sin CompanyAccount Active vigente → 403 + CTA renovación.
 */
class EnsureCompanyAccountIsActive
{
    /** @var list<string> */
    private const WHITELIST = [
        'admin.account.show',
        'admin.account.company-accounts.table',
        'admin.account.renew',
        'admin.account.renew.store',
        'companies.select',
        'companies.select.store',
        'companies.switch',
        'logout',
        'onboarding.company',
        'onboarding.company.store',
        'locale.update',
    ];

    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if ($routeName !== null && in_array($routeName, self::WHITELIST, true)) {
            return $next($request);
        }

        $companyId = $this->currentCompany->id();

        if ($companyId === null) {
            return $next($request);
        }

        if ($this->hasActiveCompanyAccount($companyId)) {
            return $next($request);
        }

        return Inertia::render('Errors/CompanyAccountRequired', [
            'messageKey' => 'cuenta_empresa_caducada',
            'renewUrl' => route('admin.account.renew', absolute: false),
        ])->toResponse($request)->setStatusCode(403);
    }

    private function hasActiveCompanyAccount(int $companyId): bool
    {
        $today = now()->toDateString();

        return CompanyAccount::query()
            ->withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('status', CompanyAccountStatus::Active)
            ->where(function ($query) use ($today): void {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->exists();
    }
}
