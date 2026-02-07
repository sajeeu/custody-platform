<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Middleware\HandleCors;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
        EnsureFrontendRequestsAreStateful::class,
        HandleCors::class,
    ]);

        $middleware->validateCsrfTokens(except: [
        'api/auth/login',
        'api/auth/logout',
        'api/admin/ledger/post',
    ]);


    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
