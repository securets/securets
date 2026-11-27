<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

// The admin console is the single Blade view at admin/web/dashboard.blade.php.
// It is rendered from its own path, so there is no second copy to keep in sync.
Route::get('/admin', [AdminDashboardController::class, 'dashboard'])
    ->middleware(EnsureAdmin::class)
    ->name('admin.dashboard');

// ── Admin Web Routes (folder: routes/admin/) ──
require __DIR__.'/admin/admin.php';
