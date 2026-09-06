<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fija CurrentCompany desde la sesión/header/usuario.
 * Hasta Admin: acepta X-Company-Id o session key; no inventa Company.
 */
class SetCurrentCompany
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->header('X-Company-Id')
            ?? $request->session()->get('current_company_id');

        if ($companyId !== null && $companyId !== '') {
            $this->currentCompany->set((int) $companyId);
        }

        return $next($request);
    }
}
