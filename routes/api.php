<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API Routes
Route::post('/login', [AuthController::class, 'login']);

// Protected API Routes (Sanctum Token Guard)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'user' => $request->user(),
        ]);
    });

    Route::get('/dashboard-data', [DataController::class, 'dashboard']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
