<?php

use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdminPanelAccess::class,
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        /* Keep CLI failures readable: Laravel's HTML error page is unreadable in
         * the terminal and in the WASM runner, so console errors stay plain. */
        $exceptions->render(function (Throwable $e, Request $request) {
            if (app()->runningInConsole() && ! $request->expectsJson()) {
                return null;
            }

            return null;
        });

        /* Test-only: surface the real message instead of a generic 500 page. */
        $exceptions->render(function (Throwable $e, Request $request) {
            if (app()->environment('testing') && $request->is('__probe/*')) {
                return response()->json([
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile().':'.$e->getLine(),
                ], 500);
            }

            return null;
        });
    })->create();
