<?php

use App\Http\Middleware\EnsureCompanySelected;
use App\Http\Middleware\EnsureModuleIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetCurrentCompany;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/domains/admin.php'));
            Route::middleware('web')
                ->group(base_path('routes/domains/partners.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetCurrentCompany::class,
            HandleInertiaRequests::class,
        ]);

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
