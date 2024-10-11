<?php

namespace App\Policies;
use App\Models\User;
use App\Models\Donations;


class DonationPolicy
{
    
    // Allow any authenticated user to view any donation
    public function viewAny(User $user)
    {
        return true; // Any user can view all donations
    }

    public function view(User $user, Donations $donation)
    {
        return true; // Any user can view a specific donation
    }

    // Only admin can create donations
    public function create(User $user)
    {
        return $user->user_type === 'admin';
    }

    // Only admin can update donations
    public function update(User $user, Donations $donation)
    {
        return $user->user_type === 'admin';
    }

    // Only admin can delete donations
    public function delete(User $user, Donations $donation)
    {
        return $user->user_type === 'admin';
    }
}
