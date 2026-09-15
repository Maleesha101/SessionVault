<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\User;
use App\Services\SecurityEventService;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * View admin dashboard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        $users = User::select(['id', 'name', 'email', 'role', 'is_disabled'])
            ->latest('created_at')
            ->get();

        $sessions = Session::select(['id', 'user_id', 'last_activity', 'created_at'])
            ->whereNotNull('user_id')
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function ($session) {
                $user = User::where('id', $session->user_id)->first();
                return [
                    'id' => $session->id,
                    'user_id' => $session->user_id,
                    'user_name' => $user ? $user->name : 'Unknown',
                    'last_activity' => $session->last_activity,
                    'created_at' => $session->created_at,
                    'fingerprint' => SessionService::shortFingerprint($session->id),
                ];
            });

        return response()->json([
            'users' => $users,
            'sessions' => $sessions,
        ]);
    }

    /**
     * View users list.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function users(Request $request): JsonResponse
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

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Disable a user account.
     *
     * @param Request $request
     * @param string $userId
     * @return JsonResponse
     */
    public function disable(Request $request, string $userId): JsonResponse
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
            return response()->json([
                'message' => 'User not found',
                'error' => 'USER_NOT_FOUND',
            ], 404);
        }

        if ($targetUser->is_disabled) {
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

        return response()->json([
            'message' => 'User disabled successfully',
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
            ],
        ]);
    }
}