<?php

use App\Http\Middleware\EnsureModuleIsActive;
use App\Support\Modules\ModuleActivationChecker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('permite el paso cuando el checker dice activo', function () {
    $checker = new class implements ModuleActivationChecker
    {
        public function isActive(string $module): bool
        {
            return $module === 'partners';
        }
    };

    $middleware = new EnsureModuleIsActive($checker);
    $request = Request::create('/partners');

    $response = $middleware->handle($request, fn () => new Response('ok'), 'partners');

    expect($response->getContent())->toBe('ok');
});

it('aborta 403 cuando el módulo está inactivo', function () {
    $checker = new class implements ModuleActivationChecker
    {
        public function isActive(string $module): bool
        {
            return false;
        }
    };

    $middleware = new EnsureModuleIsActive($checker);
    $request = Request::create('/partners');

    $middleware->handle($request, fn () => new Response('ok'), 'partners');
})->throws(HttpException::class);
