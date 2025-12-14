<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TaskGroup;
use App\Models\Board;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskGroupPolicy
{
    use HandlesAuthorization;

    /**
     * Get user's role on specific board
     */
    protected function getUserRole(User $user, Board $board): ?string
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member?->pivot?->role;
    }

    /**
     * Determine if user can update the task group (move status, rename)
     */
    public function update(User $user, TaskGroup $group): bool
    {
        $role = $this->getUserRole($user, $group->board);
        
        if (!$role) {
            return false; // Not a member
        }

        // Only Owner and Admin can manage groups - members are view-only
        return in_array($role, ['owner', 'admin']);
    }
    
    /**
     * Determine if user can delete the group
     */
    public function delete(User $user, TaskGroup $group): bool
    {
         $role = $this->getUserRole($user, $group->board);
         // Only Owner/Admin can delete groups (Main Tasks) to prevent accidental data loss
         return in_array($role, ['owner', 'admin']);
    }
}
