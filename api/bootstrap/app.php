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

       $middleware->redirectGuestsTo(fn (\Illuminate\Http\Request $request) => null);

        $middleware->validateCsrfTokens(except: [
        'api/auth/login',
        'api/auth/logout',
        'api/admin/ledger/post',
        'api/withdrawals/request',
        'api/withdrawals/*/approve',
        'api/withdrawals/*/reject',
        'api/admin/deposits',
        'api/admin/allocated-deposits',
        'api/withdrawals/request-allocated',
        'api/admin/withdrawals/*/release-bars',
        'api/deposits',
    ]);


    })
->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions): void {
    $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Non-API requests (if you ever add web pages later)
        return null;
    });
})

    
    ->create();
