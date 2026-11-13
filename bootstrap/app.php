<?php

use App\Http\Middleware\EnsureCompanySelected;
use App\Http\Middleware\EnsureModuleIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetCurrentCompany;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Route;

// Apache en Windows comparte el proceso entre hilos. putenv es global y, en
// paralelo con el polling del layout, deja APP_KEY vacío en alguna petición.
Env::disablePutenv();

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/domains/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            SetCurrentCompany::class,
            HandleInertiaRequests::class,
        ]);

        // CurrentCompany debe existir antes del route binding (p. ej. {companyRole}).
        // Si no, binds que lean el tenant abortan 404 en requests reales.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: SetCurrentCompany::class,
        );

        $middleware->alias([
            'current_company' => SetCurrentCompany::class,
            'company' => EnsureCompanySelected::class,
            'module' => EnsureModuleIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
