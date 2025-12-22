<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TaskGroup;
use App\Models\Board;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Restricts task group modifications to Owner/Admin to protect board structure.
 * 
 * Task groups represent high-level organization (columns/sections) and
 * should not be modified by regular members to prevent board disruption.
 */
class TaskGroupPolicy
{
    use HandlesAuthorization;

    /**
     * Get the user's role on a specific board.
     *
     * @param User $user
     * @param Board $board
     * @return string|null
     */
    protected function getUserRole(User $user, Board $board): ?string
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member?->pivot?->role;
    }

    /**
     * Only Owner and Admin can rename or move task groups.
     */
    public function update(User $user, TaskGroup $group): bool
    {
        $role = $this->getUserRole($user, $group->board);
        
        if (!$role) {
            return false;
        }

        return in_array($role, ['owner', 'admin']);
    }
    
    /**
     * Only Owner and Admin can delete task groups to prevent accidental data loss.
     */
    public function delete(User $user, TaskGroup $group): bool
    {
         $role = $this->getUserRole($user, $group->board);
         return in_array($role, ['owner', 'admin']);
    }
}

