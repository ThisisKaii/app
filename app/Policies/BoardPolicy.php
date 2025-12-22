<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Board;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines authorization rules for Board-level actions.
 * 
 * Role hierarchy: Owner > Admin > Member
 * - Owner: Full control including deletion and member management
 * - Admin: Can manage tasks, budgets, and invite/remove members
 * - Member: View-only access with ability to add expenses
 */
class BoardPolicy
{
    use HandlesAuthorization;

    /**
     * Any board member can view the board.
     */
    public function view(User $user, Board $board)
    {
        return $board->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Any board member can view tasks on the board.
     */
    public function viewTasks(User $user, Board $board)
    {
        return $board->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Only Owner and Admin can modify board settings.
     */
    public function update(User $user, Board $board)
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Only the Owner can permanently delete the board.
     */
    public function delete(User $user, Board $board)
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'owner';
    }

    /**
     * Owner and Admin can invite new members to the board.
     */
    public function addMember(User $user, Board $board)
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Owner and Admin can remove members from the board.
     */
    public function removeMember(User $user, Board $board)
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Owner and Admin can create budget categories (used for Business boards).
     */
    public function createTask(User $user, Board $board)
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Owner and Admin can modify budget category structure.
     */
    public function updateTask(User $user, Board $board)
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * Owner and Admin can delete budget categories.
     */
    public function deleteTask(User $user, Board $board)
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    /**
     * All members can add expenses for collaborative budget tracking.
     */
    public function addExpense(User $user, Board $board)
    {
        return $board->members()->where('user_id', $user->id)->exists();
    }
}