<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;
use Illuminate\Auth\Access\HandlesAuthorization;

class BoardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the board.
     */
    public function view(User $user, Board $board)
    {
        // Check if user is a member of the board
        return $board->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can view tasks on the board.
     */
    public function viewTasks(User $user, Board $board)
    {
        // Any board member can view tasks
        return $board->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can update the board.
     */
    public function update(User $user, Board $board)
    {
        // Check if user is owner or admin
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can delete the board.
     */
    public function delete(User $user, Board $board)
    {
        // Only owner can delete
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'owner';
    }

    /**
     * Determine if the user can add members to the board.
     */
    public function addMember(User $user, Board $board)
    {
        // Owner and admin can add members
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can remove members from the board.
     */
    public function removeMember(User $user, Board $board)
    {
        // Owner and admin can remove members
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can create tasks on the board.
     */
    public function createTask(User $user, Board $board)
    {
        // Only owner and admin can create tasks
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can update tasks on the board.
     */
    public function updateTask(User $user, Board $board)
    {
        // Only owner and admin can update tasks - members are view-only
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Determine if the user can delete tasks on the board.
     */
    public function deleteTask(User $user, Board $board)
    {
        // Only owner and admin can delete tasks
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }
}