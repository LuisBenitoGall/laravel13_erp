<?php

namespace App\Http\Middleware;

use App\Support\Modules\ModuleActivationChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alias de ruta `module:` — 403 si el módulo no está activo para la empresa.
 */
class EnsureModuleIsActive
{
    public function __construct(
        private readonly ModuleActivationChecker $checker,
    ) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! $this->checker->isActive($module)) {
            abort(403, "Module [{$module}] is not active for the current company.");
        }

        return $next($request);
    }
}
