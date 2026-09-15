<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SessionController;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/me', [AuthController::class, 'me']);
Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

// Protected API routes - require authentication
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderId}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

    Route::get('/sessions', [SessionController::class, 'index']);
    Route::delete('/sessions/{sessionId}', [SessionController::class, 'revoke']);
    Route::delete('/sessions/all', [SessionController::class, 'revokeAll']);
});

// VULNERABILITY: SM-09
// Legacy route with session token in URL
Route::get('/legacy/profile', function () {
    $session = request()->query('session');

    if (! $session) {
        return response()->json([
            'error' => 'Missing session parameter',
        ], 400);
    }

    return response()->json([
        'warning' => 'Session token detected in URL',
        'session_id' => $session,
        'session_fingerprint' => substr(hash('sha256', $session), 0, 12),
    ]);
})->name('legacy.profile');
