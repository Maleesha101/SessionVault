<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Get the current user's profile.
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
            return view('profile', ['user' => $user]);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_disabled' => $user->is_disabled,
            ],
        ]);
    }

    /**
     * Update the current user's profile.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        if ($this->wantsHtml($request)) {
            return redirect('/profile')->with('message', 'Profile updated successfully.');
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    private function wantsHtml(Request $request): bool
    {
        return ! $request->expectsJson() && ! $request->is('api/*');
    }
}
