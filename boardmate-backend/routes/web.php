<?php

use Illuminate\Support\Facades\Route;

// Root route - API information
Route::get('/', function () {
    return response()->json([
        'message' => 'BoardMate API Server',
        'version' => '1.0.0',
        'status' => 'running',
        'endpoints' => [
            'api' => '/api',
            'health' => '/health'
        ],
        'documentation' => 'This is a pure API server. All endpoints are available under /api/*'
    ]);
});

// Health check route for monitoring
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'BoardMate API'
    ]);
});

// All other functionality is handled via API routes in routes/api.php
