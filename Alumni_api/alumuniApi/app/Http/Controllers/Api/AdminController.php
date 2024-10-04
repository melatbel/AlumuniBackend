<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    // Method to get all users
    public function getAllUsers()
    {
        $users = User::all(); // Fetch all users from the database
        return response()->json($users);
    }

    // Method to approve a user (as before)
    public function approve(User $user)
    {
        $user->is_approved = true;
        $user->save();

        return response()->json(['message' => 'User approved successfully.']);
    }
}
