<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Middleware\EmergencyLockdownMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Dedicated routes for the Secure ThreatShield Admin Web Dashboard.
| Account Recovery, Lost Device Kill Switch, Notifications, and Settings.
| The dashboard itself is admin/web/dashboard.blade.php (see routes/web.php).
|
*/

Route::middleware(EmergencyLockdownMiddleware::class)->prefix('admin')->group(function () {
    Route::get('/api/overview', [AdminDashboardController::class, 'overview'])->name('admin.api.overview');
    Route::post('/api/devices/{device}/action', [AdminDashboardController::class, 'deviceAction'])->name('admin.api.devices.action');
    Route::post('/api/simulate-event', [AdminDashboardController::class, 'simulateEvent'])->name('admin.api.simulate-event');
    Route::post('/api/system/lockdown', [AdminDashboardController::class, 'lockdown'])->name('admin.api.system.lockdown');
    Route::post('/api/system/unlock', [AdminDashboardController::class, 'unlock'])->name('admin.api.system.unlock');
    Route::get('/users', fn () => view('admin.section', ['section' => 'users']))->name('admin.users');
    Route::get('/sessions', fn () => view('admin.section', ['section' => 'sessions']))->name('admin.sessions');
    Route::get('/recovery', fn () => view('admin.section', ['section' => 'recovery']))->name('admin.recovery');
    Route::get('/audit', fn () => view('admin.section', ['section' => 'audit']))->name('admin.audit');
    Route::get('/status', fn () => view('admin.section', ['section' => 'status']))->name('admin.status');
    // ── Security Notifications Center ──
    Route::get('/notifications', [AdminDashboardController::class, 'notifications'])->name('admin.notifications');

    // ── Admin Security & Recovery Settings ──
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('admin.settings');

    // ── Action Endpoints ──
    Route::post('/recovery/generate-code', [AdminDashboardController::class, 'generateBypassCode'])->name('admin.recovery.generate');
    Route::post('/devices/lockdown', [AdminDashboardController::class, 'lockdownDevice'])->name('admin.devices.lockdown');
    Route::post('/settings/save', [AdminDashboardController::class, 'saveSettings'])->name('admin.settings.save');
});
