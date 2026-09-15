<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\User;
use App\Services\SessionService;
use App\Services\SecurityEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    /**
     * List all sessions for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $sessions = Session::where('user_id', $user->id)
            ->select(['id', 'last_activity', 'ip_address', 'user_agent', 'is_current', 'created_at'])
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'fingerprint' => SessionService::shortFingerprint($session->id),
                    'created_at' => $session->created_at,
                    'last_activity' => $session->last_activity,
                    'is_current' => $session->is_current,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                ];
            });

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    /**
     * Get the current session details.
     */
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $session = Session::where('user_id', $user->id)
            ->where('is_current', true)
            ->first();

        return response()->json([
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
     * Revoke an individual session.
     */
    public function revoke(Request $request, string $sessionId): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Do not allow revoking the current session if there are no other sessions.
        $otherSessions = Session::where('user_id', $user->id)
            ->where('id', '!=', $sessionId)
            ->count();

        if ($otherSessions === 0) {
            return response()->json([
                'message' => 'Cannot revoke the last active session',
                'error' => 'LAST_SESSION',
            ], 400);
        }

        SessionService::invalidate($sessionId);

        SecurityEventService::log('SESSION_REVOKED', $user->id, $sessionId, [], $request);

        return response()->json([
            'message' => 'Session revoked successfully',
        ]);
    }

    /**
     * Revoke all other sessions for the user.
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        SessionService::invalidateAllForUser($user->id);

        // Recreate only the current session.
        SessionService::createSession($user, $request);

        SecurityEventService::log('SESSION_REVOKED_ALL', $user->id, [], [], $request);

        return response()->json([
            'message' => 'All other sessions revoked successfully',
        ]);
    }
}