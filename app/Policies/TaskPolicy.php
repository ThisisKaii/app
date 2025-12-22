<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use App\Models\Board;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Defines granular authorization rules for Task-level actions.
 * 
 * Access rules:
 * - Owner/Admin: Full access to all tasks on the board
 * - Member: Can only modify tasks they created or are assigned to
 */
class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Get the user's role on a specific board.
     *
     * @param User $user
     * @param Board $board
     * @return string|null Role name or null if not a member
     */
    protected function getUserRole(User $user, Board $board): ?string
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member?->pivot?->role;
    }

    /**
     * Check if user has elevated privileges (Owner or Admin).
     *
     * @param User $user
     * @param Board $board
     * @return bool
     */
    protected function isOwnerOrAdmin(User $user, Board $board): bool
    {
        $role = $this->getUserRole($user, $board);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if the task is assigned to the user via task_user pivot or legacy assignee_id.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    protected function isAssignedToUser(User $user, Task $task): bool
    {
        if ($task->users()->where('user_id', $user->id)->exists()) {
            return true;
        }
        return $task->assignee_id === $user->id;
    }

    /**
     * Check if the user originally created the task.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    protected function isCreator(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    /**
     * Any board member can view the task list.
     */
    public function viewAny(User $user, Board $board): bool
    {
        return $this->getUserRole($user, $board) !== null;
    }

    /**
     * Any board member can view a specific task.
     */
    public function view(User $user, Task $task): bool
    {
        return $this->getUserRole($user, $task->board) !== null;
    }

    /**
     * Any board member can create new tasks.
     */
    public function create(User $user, Board $board): bool
    {
        return $this->getUserRole($user, $board) !== null;
    }

    /**
     * Owner/Admin can edit any task; Members can only edit their own tasks.
     */
    public function update(User $user, Task $task): bool
    {
        $board = $task->board;
        $role = $this->getUserRole($user, $board);
        
        if (!$role) {
            return false;
        }

        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }

        return $this->isAssignedToUser($user, $task) || $this->isCreator($user, $task);
    }

    /**
     * Owner/Admin can delete any task; Members can only delete their own tasks.
     */
    public function delete(User $user, Task $task): bool
    {
        $board = $task->board;
        $role = $this->getUserRole($user, $board);
        
        if (!$role) {
            return false;
        }

        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }

        return $this->isAssignedToUser($user, $task) || $this->isCreator($user, $task);
    }

    /**
     * Only Owner and Admin can assign tasks to other users.
     */
    public function assign(User $user, Task $task): bool
    {
        $board = $task->board;
        $role = $this->getUserRole($user, $board);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Users with update permission can also move tasks between columns.
     */
    public function moveStatus(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Users with update permission can manage task checklists.
     */
    public function manageChecklist(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}

