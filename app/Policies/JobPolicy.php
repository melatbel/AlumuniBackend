<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobPolicy
{
    /**
     * Determine whether the user can view any jobs.
     */
    public function viewAny(User $user)
    {
        return true; // Allow all users to view job listings
    }

    /**
     * Determine whether the user can view a specific job.
     */
    public function view(User $user, Job $job)
    {
        return true; // Allow all users to view job details
    }

    /**
     * Determine whether the user can create a job post.
     */
    public function create(User $user)
    {
        // Allow both alumni and admin to create job posts
        return in_array($user->user_type, ['alumni', 'admin']);
    }

    /**
     * Determine whether the user can update a job post.
     */
    public function update(User $user, Job $job)
    {
        // Allow both alumni and admin to update job posts
        return $user->id === $job->posted_by || $user->user_type === 'admin';
    }

    /**
     * Determine whether the user can delete a job post.
     */
    public function delete(User $user, Job $job)
    {
        return $user->id === $job->posted_by || $user->user_type === 'admin';
    }
}
