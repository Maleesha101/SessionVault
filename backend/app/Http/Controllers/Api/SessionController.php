<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Services\SessionService;
use App\Services\SecurityEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SessionController extends Controller
{
    /**
     * List all sessions for the current user.
     */
    public function index(Request $request): JsonResponse|View
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $sessions = $this->sessionsForUser($user->id);
        $currentSession = $sessions->firstWhere('is_current', true);

        if ($this->wantsHtml($request)) {
            return view('sessions', [
                'sessions' => $sessions,
                'currentSession' => $currentSession,
            ]);
        }

        return response()->json([
            'sessions' => $sessions->map(fn ($session) => $this->sessionPayload($session)),
        ]);
    }

    /**
     * Get the current session details (API) or full sessions page (web).
     */
    public function show(Request $request): JsonResponse|View
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        if ($this->wantsHtml($request)) {
            $sessions = $this->sessionsForUser($user->id);

            return view('sessions', [
                'sessions' => $sessions,
                'currentSession' => $sessions->firstWhere('is_current', true),
            ]);
        }

        $session = Session::where('user_id', $user->id)
            ->where('is_current', true)
            ->first();

        return response()->json([
            'session' => $session ? $this->sessionPayload($session) : null,
        ]);
    }

    /**
     * Revoke an individual session.
     */
    public function revoke(Request $request, string $sessionId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Do not allow revoking the last active session.
        $otherSessions = Session::where('user_id', $user->id)
            ->where('id', '!=', $sessionId)
            ->count();

        if ($otherSessions === 0) {
            if ($this->wantsHtml($request)) {
                return redirect('/sessions')->with('error', 'Cannot revoke the last active session.');
            }

            return response()->json([
                'message' => 'Cannot revoke the last active session',
                'error' => 'LAST_SESSION',
            ], 400);
        }

        SessionService::invalidate($sessionId);

        SecurityEventService::log('SESSION_REVOKED', $user->id, $sessionId, [], $request);

        if ($this->wantsHtml($request)) {
            return redirect('/sessions')->with('message', 'Session revoked successfully.');
        }

        return response()->json([
            'message' => 'Session revoked successfully',
        ]);
    }

    /**
     * Revoke all other sessions for the user.
     */
    public function revokeAll(Request $request): JsonResponse|RedirectResponse
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

        if ($this->wantsHtml($request)) {
            return redirect('/sessions')->with('message', 'All other sessions revoked successfully.');
        }

        return response()->json([
            'message' => 'All other sessions revoked successfully',
        ]);
    }

    private function wantsHtml(Request $request): bool
    {
        return ! $request->expectsJson() && ! $request->is('api/*');
    }

    private function sessionsForUser(int $userId)
    {
        return Session::where('user_id', $userId)
            ->select(['id', 'last_activity', 'ip_address', 'user_agent', 'is_current', 'created_at'])
            ->latest('last_activity')
            ->get()
            ->each(function ($session) {
                $session->setAttribute(
                    'fingerprint',
                    SessionService::shortFingerprint($session->id)
                );
            });
    }

    private function sessionPayload(Session $session): array
    {
        return [
            'id' => $session->id,
            'fingerprint' => $session->fingerprint ?? SessionService::shortFingerprint($session->id),
            'created_at' => $session->created_at,
            'last_activity' => $session->last_activity,
            'is_current' => $session->is_current,
            'ip_address' => $session->ip_address,
            'user_agent' => $session->user_agent,
        ];
    }
}
