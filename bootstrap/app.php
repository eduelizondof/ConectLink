<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authorization exceptions for Inertia requests
        $exceptions->render(function (\Throwable $exception, \Illuminate\Http\Request $request) {
            // Handle validation exceptions for Inertia requests
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                // For Inertia requests, return a redirect with errors
                // This allows Inertia to handle the validation errors properly
                if ($request->header('X-Inertia') || $request->header('X-Inertia-Version')) {
                    return redirect()->back()->withErrors($exception->errors())->withInput();
                }
                
                // For API requests, return JSON
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors' => $exception->errors(),
                    ], 422);
                }
                
                // For regular requests, let Laravel handle it
                return null;
            }
            
            if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                // Check if it's an Inertia request
                if ($request->header('X-Inertia') || $request->header('X-Inertia-Version')) {
                    return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
                }
                
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'No tienes permiso para realizar esta acción.',
                        'error' => $exception->getMessage(),
                    ], 403);
                }

                // For regular requests
                abort(403, 'No tienes permiso para realizar esta acción.');
            }
            
            // Return null to let Laravel handle all other exceptions with default handler
            return null;
        });
    })->create();
