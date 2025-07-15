<?php

use App\Http\Middleware\CheckBearerToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        //$middleware->append(CheckBearerToken::class);

        $middleware->appendToGroup('app_user_middleware', [
            CheckBearerToken::class,
        ]);

        $middleware->prependToGroup('app_user_middleware', [
            CheckBearerToken::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                // 'message' => 'Method Not Allowed. Allowed methods: ' . implode(', ', $e->getHeaders()['Allow'] ?? []),
                'message' => 'Method Not Allowed. Allowed methods: ',

            ], 405);
        }
        // Return null to fallback to default rendering for non-API or non-JSON requests
        return null;
    });
    })->create();
