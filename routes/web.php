<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// ── Landing page ──────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── Auth routes ───────────────────────────────────────────────────────────────
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register']);

Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

// Public login gate for a linked app (uses gate_token, not the TOTP secret)
Route::get('/i/{integration:gate_token}', [IntegrationController::class, 'gateShow'])->name('gate.show');
Route::post('/i/{integration:gate_token}', [IntegrationController::class, 'gateAttempt'])->name('gate.attempt');
Route::get('/i/{integration:gate_token}/events/{event}', [IntegrationController::class, 'gateEventStatus'])->name('gate.event');

// ── Protected routes ──────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',    [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/integrations', [IntegrationController::class, 'webIndex'])->name('integrations');

    // Account & Preferences
    Route::get('/profile',            [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',   [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/archive',   [ProfileController::class, 'archive'])->name('profile.archive');

    Route::get('/settings',           [SettingsController::class, 'edit'])->name('settings');
    Route::patch('/settings',         [SettingsController::class, 'update'])->name('settings.update');

    // ── Web-only JSON endpoints (session-authenticated, used by integrations.blade.php) ──
    Route::prefix('web')->group(function () {
        // Integrations CRUD
        Route::post('/integrations',                           [IntegrationController::class, 'store']);
        Route::delete('/integrations/{integration}',           [IntegrationController::class, 'destroy']);
        Route::patch('/integrations/{integration}/toggle',     [IntegrationController::class, 'toggle']);
        Route::get('/integrations/{integration}/totp',         [IntegrationController::class, 'totpCode']);

        // Read-only history on web. Approve / decline stays on the mobile app.
        Route::get('/login-events',                            [IntegrationController::class, 'history']);
    });
});

// ── Public API endpoints (status / health) ────────────────────────────────────
Route::prefix('api')->group(function () {
    Route::get('/status', function () {
        return response()->json([
            'status'       => 'Operational',
            'protection'   => 'ThreatShield Mobile v2.4',
            'splash_delay' => '5.0 Seconds',
            'backend'      => 'PHP Laravel 12 API',
            'timestamp'    => now()->toIso8601String(),
        ]);
    });

    Route::get('/health', function () {
        return response()->json([
            'healthy' => true,
            'service' => 'SecureHub ThreatShield API',
        ]);
    });
});
