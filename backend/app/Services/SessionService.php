<?php

namespace App\Services;

use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionService
{
    public const COOKIE_NAME = 'sessionvault_session';

    /**
     * Generate a session identifier.
     *
     * INTENTIONAL VULNERABILITY: SM-01
     * When VULN_PREDICTABLE_SESSION=true, session IDs are predictable
     * sequential values: session-000001, session-000002, etc.
     *
     * When false, cryptographically secure random IDs are used.
     */
    public static function generateSessionId(): string
    {
        if (env('VULN_PREDICTABLE_SESSION', true)) {
            $count = Session::count() + 1;
            return 'session-' . str_pad($count, 6, '0', STR_PAD_LEFT);
        }

        return 'sess_' . Str::random(32);
    }

    /**
     * Create a new session record for a user.
     */
    public static function createSession(User $user, Request $request): Session
    {
        $sessionId = self::generateSessionId();

        $session = Session::create([
            'id' => $sessionId,
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity' => now(),
            'is_current' => true,
        ]);

        // Mark all other sessions for this user as not current.
        Session::where('user_id', $user->id)
            ->where('id', '!=', $sessionId)
            ->update(['is_current' => false]);

        return $session;
    }

    /**
     * Rotate the session identifier after authentication.
     *
     * INTENTIONAL VULNERABILITY: SM-03
     * When VULN_NO_SESSION_ROTATION=true, the session ID is NOT changed
     * after login (no rotation). When false, old session is destroyed
     * and a new one is created.
     */
    public static function rotateSession(User $user, string $oldSessionId, Request $request): Session
    {
        if (env('VULN_NO_SESSION_ROTATION', true)) {
            $session = Session::where('id', $oldSessionId)->first();
            if ($session) {
                $session->update(['last_activity' => now()]);
            }
            return $session ?? self::createSession($user, $request);
        }

        // Secure: invalidate old session and create new one.
        Session::where('id', $oldSessionId)->delete();
        return self::createSession($user, $request);
    }

    /**
     * Get a full fingerprint of a session ID.
     */
    public static function fingerprint(string $sessionId): string
    {
        return hash('sha256', $sessionId);
    }

    /**
     * Get a short fingerprint for safe UI display.
     */
    public static function shortFingerprint(string $sessionId): string
    {
        return substr(hash('sha256', $sessionId), 0, 12);
    }

    /**
     * Invalidate a session by ID.
     */
    public static function invalidate(string $sessionId): bool
    {
        $session = Session::find($sessionId);
        if ($session) {
            $session->delete();
            return true;
        }
        return false;
    }

    /**
     * Invalidate all sessions for a user.
     */
    public static function invalidateAllForUser(int $userId): int
    {
        return Session::where('user_id', $userId)->delete();
    }
}
