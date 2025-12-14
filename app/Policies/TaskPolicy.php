<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use App\Models\Board;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Get user's role on a specific board
     */
    protected function getUserRole(User $user, Board $board): ?string
    {
        $member = $board->members()->where('user_id', $user->id)->first();
        return $member?->pivot?->role;
    }

    /**
     * Check if user is owner or admin on the board
     */
    protected function isOwnerOrAdmin(User $user, Board $board): bool
    {
        $role = $this->getUserRole($user, $board);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if task is assigned to user (via task_user or assignee_id)
     */
    protected function isAssignedToUser(User $user, Task $task): bool
    {
        // Check new multi-assignee relationship
        if ($task->users()->where('user_id', $user->id)->exists()) {
            return true;
        }
        
        // Fallback to old single assignee
        return $task->assignee_id === $user->id;
    }

    /**
     * Check if user created the task
     */
    protected function isCreator(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    /**
     * Determine if user can view any tasks on the board
     */
    public function viewAny(User $user, Board $board): bool
    {
        // Any board member can view tasks
        return $this->getUserRole($user, $board) !== null;
    }

    /**
     * Determine if user can view a specific task
     */
    public function view(User $user, Task $task): bool
    {
        // Any board member can view any task
        return $this->getUserRole($user, $task->board) !== null;
    }

    /**
     * Determine if user can create tasks on the board
     */
    public function create(User $user, Board $board): bool
    {
        // Any board member can create tasks
        return $this->getUserRole($user, $board) !== null;
    }

    /**
     * Determine if user can update a task
     */
    public function update(User $user, Task $task): bool
    {
        $board = $task->board;
        $role = $this->getUserRole($user, $board);
        
        if (!$role) {
            return false;
        }

        // Owner and Admin can edit any task
        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }

        // Members can only edit their own tasks (assigned or created)
        return $this->isAssignedToUser($user, $task) || $this->isCreator($user, $task);
    }

    /**
     * Determine if user can delete a task
     */
    public function delete(User $user, Task $task): bool
    {
        $board = $task->board;
        $role = $this->getUserRole($user, $board);
        
        if (!$role) {
            return false;
        }

        // Owner and Admin can delete any task
        if (in_array($role, ['owner', 'admin'])) {
            return true;
        }

        // Members can only delete their own tasks
        return $this->isAssignedToUser($user, $task) || $this->isCreator($user, $task);
    }

    /**
     * Determine if user can assign tasks to others
     */
    public function assign(User $user, Task $task): bool
    {
        $board = $task->board;
        $role = $this->getUserRole($user, $board);
        
        // Only Owner and Admin can assign to others
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if user can change task status (drag-drop)
     */
    public function moveStatus(User $user, Task $task): bool
    {
        // Same rules as update - can only move your own tasks as Member
        return $this->update($user, $task);
    }

    /**
     * Determine if user can manage checklist items
     */
    public function manageChecklist(User $user, Task $task): bool
    {
        // Users can manage checklist on tasks assigned to them
        return $this->update($user, $task);
    }
}
