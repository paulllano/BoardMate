<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS is configured in config/cors.php
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions - return JSON for API routes
        $exceptions->render(function (AuthenticationException $e, \Illuminate\Http\Request $request) {
            // Always return JSON for API routes
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            // For non-API routes, return JSON as well (pure API server)
            return response()->json(['message' => 'Unauthenticated.'], 401);
        });
        
        // Handle all other exceptions - return JSON
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Always return JSON for API routes
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ] : null
                ], $e->getCode() ?: 500);
            }
            
            // For non-API routes, also return JSON (pure API server)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], $e->getCode() ?: 500);
        });
    })->create();
