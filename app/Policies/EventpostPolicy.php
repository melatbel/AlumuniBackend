<?php

namespace App\Policies;

use App\Models\Eventpost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventpostPolicy
{
    use HandlesAuthorization;
    use HandlesAuthorization;

    /**
     * Determine if the authenticated user can view any event posts.
     */
    public function viewAny(User $user)
    {
        // Allow anyone to view events (you can modify if needed)
        return true;
    }

    /**
     * Determine if the given event post can be viewed by the user.
     */
    public function view(User $user, Eventpost $eventpost)
    {
        // Allow anyone to view events
        return true;
    }

    /**
     * Determine if the authenticated user can create an event post.
     */
    public function create(User $user)
    {
        // Allow alumni and admin to create event posts
        return $user->user_type === 'alumni' || $user->user_type === 'admin';
    }

    /**
     * Determine if the given event post can be updated by the user.
     */
    public function update(User $user, Eventpost $eventpost)
    {
        // Allow admin to update any post or the owner (alumni) of the post to update it
        return $user->user_type === 'admin' || $user->id === $eventpost->posted_by;
    }

    /**
     * Determine if the given event post can be deleted by the user.
     */
    public function delete(User $user, Eventpost $eventpost)
    {
        // Allow admin to delete any post or the owner (alumni) of the post to delete it
        return $user->user_type === 'admin' || $user->id === $eventpost->posted_by;
    }

    /**
     * Determine if the given event post can be restored by the user.
     */
    public function restore(User $user, Eventpost $eventpost)
    {
        // Allow admin or the owner to restore the post
        return $user->user_type === 'admin' || $user->id === $eventpost->posted_by;
    }

    /**
     * Determine if the given event post can be permanently deleted by the user.
     */
    public function forceDelete(User $user, Eventpost $eventpost)
    {
        // Allow admin to force delete any post
        return $user->user_type === 'admin' || $user->id === $eventpost->posted_by;
    }
}
