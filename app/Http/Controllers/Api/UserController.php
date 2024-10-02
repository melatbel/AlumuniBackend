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

}
