<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fija CurrentCompany desde sesión validando pertenencia a company activa.
 */
class SetCurrentCompany
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $rawCompanyId = $request->session()->get('current_company_id');
        $user = $request->user();

        if ($rawCompanyId === null || $rawCompanyId === '') {
            setPermissionsTeamId(null);

            return $next($request);
        }

        $id = (int) $rawCompanyId;

        if ($user !== null) {
            if ($user->belongsToCompany($id)) {
                $this->currentCompany->set($id);
                setPermissionsTeamId($id);
                // Spatie cachea roles/permisos del User; hay que invalidar al cambiar team.
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');
            } else {
                $request->session()->forget('current_company_id');
                $this->currentCompany->set(null);
                setPermissionsTeamId(null);
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');
            }
        } else {
            // Tests sin user autenticado: solo int de sesión.
            $this->currentCompany->set($id);
            setPermissionsTeamId($id);
        }

        return $next($request);
    }
}
