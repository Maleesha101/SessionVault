<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\User;
use App\Services\SecurityEventService;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
            'is_disabled' => false,
        ]);

        SecurityEventService::log('ACCOUNT_REGISTERED', $user->id);

        return response()->json([
            'message' => 'Registration successful. Please log in.',
            'user' => $user,
        ], 201);
    }

    /**
     * Log in a user and create a session.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            SecurityEventService::log('LOGIN_FAILURE', null, null, [
                'username' => $validated['email'],
                'reason' => 'Invalid credentials',
            ], $request);

            return response()->json([
                'message' => 'Invalid credentials',
                'error' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        if ($user->is_disabled) {
            SecurityEventService::log('ACCOUNT_DISABLED_LOGIN_ATTEMPT', $user->id, null, [
                'reason' => 'Account is disabled',
            ], $request);

            return response()->json([
                'message' => 'Account is disabled',
                'error' => 'ACCOUNT_DISABLED',
            ], 403);
        }

        // INTENTIONAL VULNERABILITY: SM-02 (Session Fixation)
        // If a pre-auth session exists in the cookie, reuse it after login
        // instead of creating a new one. In secure mode, always create new.
        $sessionId = $request->cookie(SessionService::COOKIE_NAME) ?? null;
        $session = null;

        if ($sessionId && Session::where('id', $sessionId)->exists()) {
            // Vulnerable: reuse the pre-auth session.
            $session = Session::where('id', $sessionId)->first();
            $session->update([
                'user_id' => $user->id,
                'last_activity' => now()->timestamp,
                'is_current' => true,
            ]);
            Session::where('user_id', $user->id)
                ->where('id', '!=', $sessionId)
                ->update(['is_current' => false]);
        } else {
            // Secure fallback: create new session.
            $session = SessionService::createSession($user, $request);
        }

        SecurityEventService::log('LOGIN_SUCCESS', $user->id, $session->id, [
            'session_fingerprint' => SessionService::shortFingerprint($session->id),
        ], $request);

        // Determine cookie attributes based on vulnerability flags
        $httpOnly = true;  // Default secure
        $secure = true;    // Default secure
        $sameSite = 'Strict';  // Default secure

        if (env('VULN_MISSING_HTTPONLY', false)) {
            $httpOnly = false;  // Vulnerable: HttpOnly disabled
        }
        if (env('VULN_MISSING_SECURE', false)) {
            $secure = false;    // Vulnerable: Secure disabled
        }
        if (env('VULN_WEAK_SAMESITE', false)) {
            $sameSite = 'Lax';  // Vulnerable: SameSite=Lax instead of Strict
        }

        $cookieValue = $session->id;
        $maxAge = env('LAB_SESSION_LIFETIME', 86400);

        $response = response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'session' => [
                'id' => $session->id,
                'fingerprint' => SessionService::shortFingerprint($session->id),
                'created_at' => $session->created_at,
                'last_activity' => $session->last_activity,
                'is_current' => $session->is_current,
            ],
        ]);

        // Set-Cookie header with potentially vulnerable attributes
        $cookieParts = [
            $cookieValue,
            'Path=/',
            'Max-Age=' . $maxAge,
            'SameSite=' . $sameSite,
        ];
        if ($httpOnly) {
            $cookieParts[] = 'HttpOnly';
        }
        if ($secure) {
            $cookieParts[] = 'Secure';
        }

        $response->headers->set('Set-Cookie', SessionService::COOKIE_NAME . '=' . implode('; ', $cookieParts));

        return $response;
    }

    /**
     * Log out the current user.
     *
     * INTENTIONAL VULNERABILITY: SM-08
     * In vulnerable mode, logout only removes the browser cookie but does
     * NOT invalidate the server-side session, so the session token
     * remains valid after logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $sessionId = $request->cookie(SessionService::COOKIE_NAME);
        $user = Auth::user();

        if ($sessionId) {
            SecurityEventService::log('LOGOUT', $user?->id, $sessionId, [], $request);

            // VULNERABLE: Do not invalidate server-side session on logout.
            // When VULN_LOGOUT_NOT_INVALIDATE=true, the session stays valid.
            if (! env('VULN_LOGOUT_NOT_INVALIDATE', true)) {
                SessionService::invalidate($sessionId);
            }
        }

        $response = response()->json([
            'message' => 'Logged out successfully',
        ]);

        $response->headers->set('Set-Cookie', SessionService::COOKIE_NAME . '=; Path=/; Max-Age=0');

        return $response;
    }

    /**
     * Get the current authenticated user info.
     */
    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $session = Session::where('id', $request->cookie(SessionService::COOKIE_NAME))
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_disabled' => $user->is_disabled,
            ],
            'session' => $session ? [
                'id' => $session->id,
                'fingerprint' => SessionService::shortFingerprint($session->id),
                'created_at' => $session->created_at,
                'last_activity' => $session->last_activity,
                'is_current' => $session->is_current,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
            ] : null,
        ]);
    }

    /**
     * Change the current user's password.
     *
     * INTENTIONAL VULNERABILITY: SM-11
     * In vulnerable mode, existing sessions for this user remain valid
     * after a password change. In secure mode, all sessions are revoked.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'error' => 'INVALID_CURRENT_PASSWORD',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        SecurityEventService::log('PASSWORD_CHANGED', $user->id, null, [
            'session_fingerprint' => SessionService::shortFingerprint($request->cookie(SessionService::COOKIE_NAME) ?? ''),
        ], $request);

        // VULNERABLE: Sessions survive password change.
        // When VULN_PASSWORD_CHANGE_INVALIDATION=true, sessions are NOT invalidated.
        if (! env('VULN_PASSWORD_CHANGE_INVALIDATION', true)) {
            SessionService::invalidateAllForUser($user->id);
        }

        return response()->json([
            'message' => 'Password changed successfully',
            'user' => $user,
        ]);
    }
}
