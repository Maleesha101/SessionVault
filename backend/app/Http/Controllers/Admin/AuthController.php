<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\Session;
use App\Models\User;
use App\Services\SecurityEventService;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * View admin dashboard.
     */
    public function dashboard(Request $request): JsonResponse|View
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        $users = User::select(['id', 'name', 'email', 'role', 'is_disabled', 'created_at'])
            ->latest('created_at')
            ->get();

        $sessions = Session::with('user:id,name')
            ->select(['id', 'user_id', 'last_activity', 'created_at', 'ip_address', 'is_current'])
            ->whereNotNull('user_id')
            ->latest('created_at')
            ->take(20)
            ->get()
            ->each(function ($session) {
                $session->setAttribute(
                    'user_name',
                    $session->user?->name ?? 'Unknown'
                );
                $session->setAttribute(
                    'fingerprint',
                    SessionService::shortFingerprint($session->id)
                );
            });

        if ($this->wantsHtml($request)) {
            return view('admin.dashboard', [
                'users' => $users,
                'sessions' => $sessions,
                'stats' => [
                    'users' => $users->count(),
                    'active_users' => $users->where('is_disabled', false)->count(),
                    'sessions' => Session::whereNotNull('user_id')->count(),
                    'admins' => $users->where('role', 'admin')->count(),
                ],
            ]);
        }

        return response()->json([
            'users' => $users->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'is_disabled' => $u->is_disabled,
            ]),
            'sessions' => $sessions->map(fn ($session) => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'user_name' => $session->user_name,
                'last_activity' => $session->last_activity,
                'created_at' => $session->created_at,
                'fingerprint' => $session->fingerprint,
            ]),
        ]);
    }

    /**
     * View users list.
     */
    public function users(Request $request): JsonResponse|View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        $users = User::select(['id', 'name', 'email', 'role', 'is_disabled', 'created_at'])
            ->latest('created_at')
            ->get();

        if ($this->wantsHtml($request)) {
            return redirect()->route('admin.dashboard');
        }

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Disable a user account.
     */
    public function disable(Request $request, string $userId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        $targetUser = User::find($userId);

        if (! $targetUser) {
            if ($this->wantsHtml($request)) {
                return redirect()->route('admin.dashboard')->with('error', 'User not found.');
            }

            return response()->json([
                'message' => 'User not found',
                'error' => 'USER_NOT_FOUND',
            ], 404);
        }

        if ($targetUser->is_disabled) {
            if ($this->wantsHtml($request)) {
                return redirect()->route('admin.dashboard')->with('error', 'User is already disabled.');
            }

            return response()->json([
                'message' => 'User is already disabled',
                'error' => 'USER_ALREADY_DISABLED',
            ], 400);
        }

        $targetUser->update(['is_disabled' => true]);

        SecurityEventService::log('ACCOUNT_DISABLED', $targetUser->id, null, [], $request);

        // VULNERABILITY: SM-12
        // In vulnerable mode, existing sessions for the disabled user remain valid.
        // In secure mode, these sessions would be invalidated.
        if (! env('VULN_ACCOUNT_DISABLE_INVALIDATION', false)) {
            SessionService::invalidateAllForUser($targetUser->id);
        }

        if ($this->wantsHtml($request)) {
            return redirect()->route('admin.dashboard')
                ->with('message', "Disabled {$targetUser->name} successfully.");
        }

        return response()->json([
            'message' => 'User disabled successfully',
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
            ],
        ]);
    }

    /**
     * Recent security events for the admin console.
     */
    public function securityEvents(Request $request): JsonResponse|View
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        $events = SecurityEvent::query()
            ->latest('created_at')
            ->take(50)
            ->get();

        if ($this->wantsHtml($request)) {
            return redirect()->route('admin.dashboard');
        }

        return response()->json(['events' => $events]);
    }

    /**
     * High-level admin stats.
     */
    public function stats(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        $stats = [
            'users' => User::count(),
            'active_users' => User::where('is_disabled', false)->count(),
            'sessions' => Session::whereNotNull('user_id')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        if ($this->wantsHtml($request)) {
            return redirect()->route('admin.dashboard');
        }

        return response()->json(['stats' => $stats]);
    }

    private function wantsHtml(Request $request): bool
    {
        return ! $request->expectsJson() && ! $request->is('api/*');
    }
}
