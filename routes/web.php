<?php

use Illuminate\Support\Facades\Route;

// REST API endpoints for React Native mobile client
Route::prefix('api')->group(function () {
    Route::get('/status', function () {
        return response()->json([
            'status' => 'Operational',
            'protection' => 'ThreatShield Mobile v2.4',
            'splash_delay' => '5.0 Seconds',
            'backend' => 'PHP Laravel 12 API',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('/health', function () {
        return response()->json([
            'healthy' => true,
            'service' => 'SecureHub ThreatShield API',
        ]);
    });
});
