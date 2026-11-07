<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Mobile Auth (public) ──────────────────────────────────────────────────────
// Keep the legacy /api/login path alive so refreshed or cached mobile clients
// can still sign back in while the app converges on /api/mobile/login.
Route::post('/login', [MobileAuthController::class, 'login']);

Route::prefix('mobile')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login']);
});

// ── Target App (secret key, not the user's Sanctum token) ─────────────────────
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/integrations/login-event', [IntegrationController::class, 'publicLoginEvent']);
    Route::get('/login-events/{event}', [IntegrationController::class, 'publicEventStatus'])
        ->whereNumber('event');
    Route::get('/login-events/{event}/wait', [IntegrationController::class, 'publicEventLongPoll'])
        ->whereNumber('event');
});

// ── Authenticated API routes (Sanctum token) ──────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/mobile/logout', [MobileAuthController::class, 'logout']);
    Route::get('/mobile/me', [MobileAuthController::class, 'me']);

    // Integrations CRUD
    Route::get('/integrations', [IntegrationController::class, 'index']);
    Route::post('/integrations', [IntegrationController::class, 'store']);
    Route::patch('/integrations/{integration}', [IntegrationController::class, 'update']);
    Route::delete('/integrations/{integration}', [IntegrationController::class, 'destroy']);
    Route::patch('/integrations/{integration}/toggle', [IntegrationController::class, 'toggle']);

    // TOTP code for a specific integration
    Route::get('/integrations/{integration}/totp', [IntegrationController::class, 'totpCode']);

    // Login events (called by external app or simulator)
    Route::post('/integrations/{integration}/login-event', [IntegrationController::class, 'loginEvent']);
    Route::patch('/login-events/{event}/respond', [IntegrationController::class, 'respondToEvent']);
    Route::patch('/login-events/{event}/archive', [IntegrationController::class, 'archiveEvent']);
    Route::delete('/login-events/{event}', [IntegrationController::class, 'destroyEvent']);
    Route::get('/login-events', [IntegrationController::class, 'history']);
    Route::get('/login-events/pending', [IntegrationController::class, 'pendingApprovals']);

    // Devices (Expo push tokens)
    Route::post('/devices',              [DeviceController::class, 'register']);
    Route::get('/devices',               [DeviceController::class, 'index']);
    Route::delete('/devices/{device}',   [DeviceController::class, 'destroy']);

    // Subscription (mobile)
    Route::get('/subscription/status',     [SubscriptionController::class, 'status']);
    Route::post('/subscription/checkout',  [SubscriptionController::class, 'createCheckoutSession']);
    Route::post('/subscription/portal',    [SubscriptionController::class, 'billingPortal']);
});

// ── Stripe Webhook (public — no auth, no CSRF) ────────────────────────────────
Route::post('/stripe/webhook', [SubscriptionController::class, 'webhook']);
