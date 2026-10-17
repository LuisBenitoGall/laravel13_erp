<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige empresa de sesión para rutas ERP (post-login 0/1/N).
 * Sin companies → onboarding (sesión preservada).
 */
class EnsureCompanySelected
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->activeCompanies()->isEmpty()) {
            return redirect()->route('onboarding.company');
        }

        if ($this->currentCompany->id() === null) {
            return redirect()->route('companies.select');
        }

        return $next($request);
    }
}
