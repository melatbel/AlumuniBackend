<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Logout api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token of the currently authenticated user
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'User successfully logged out']);
    }

    /**
     * Get user profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        // Return authenticated user's profile
        return response()->json([
            'user' => $request->user()
        ]);
    }
}
