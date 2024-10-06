<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function approveUser($id)
  {
    $user = User::find($id);
    $user->approved = true;
    $user->save();

    return redirect()->back()->with('success', 'User approved successfully');
  }

    public function showUserDetails($id)
        {
            $user = User::find($id);

            if ($user) {
                return response()->json([
                    'full_name' => $user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'batch' => $user->batch,
                    'current_profession' => $user->linkedin_profile, // assuming current profession is stored in the LinkedIn profile
                ]);
            } else {
                return response()->json(['message' => 'User not found'], 404);
            }
        }

}
