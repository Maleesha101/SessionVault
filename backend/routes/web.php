<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SessionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Web Auth routes
Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::post('/login', [AuthController::class, 'webLogin']);
Route::get('/register', function () { return view('auth.register'); })->name('register');

// Protected web routes - require authentication
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update']);

    // Orders (web view)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Sessions (web view)
    Route::get('/sessions', [SessionController::class, 'show'])->name('sessions.show');
    Route::delete('/sessions/all', [SessionController::class, 'revokeAll'])->name('sessions.revoke-all');
    Route::delete('/sessions/{sessionId}', [SessionController::class, 'revoke'])->name('sessions.revoke');

    // Admin routes - admin only
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/admin/dashboard', [AdminAuthController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/users', [AdminAuthController::class, 'users'])->name('admin.users');
        Route::post('/admin/users/{userId}/disable', [AdminAuthController::class, 'disable'])->name('admin.users.disable');
        Route::get('/admin/security-events', [AdminAuthController::class, 'securityEvents'])->name('admin.security-events');
        Route::get('/admin/stats', [AdminAuthController::class, 'stats'])->name('admin.stats');
    });
});

// VULNERABILITY: SM-09
// Legacy route with session token in URL
Route::get('/legacy/profile', function () {
    $session = request()->query('session');
    return view('legacy.profile', ['session' => $session]);
})->name('legacy.profile');
