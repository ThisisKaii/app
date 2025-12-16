<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class Individual extends Component
{
    public $boardId;

    public function mount($boardId)
    {
        $this->boardId = $boardId;
    }

    public function toggleComplete($taskId)
    {
        $task = Task::with('users')->find($taskId);
        // Basic permission check - user must be assigned to this task
        $userId = Auth::id();
        if ($task && (
            $task->assignee_id === $userId || 
            $task->users->contains('id', $userId) || 
            $task->user_id === $userId
        )) {
            $previousStatus = $task->status;
            // When unchecking (published -> in_progress), when checking (any -> published)
            $newStatus = $previousStatus === 'published' ? 'in_progress' : 'published';
            
            $task->status = $newStatus;
            $task->completed_at = $newStatus === 'published' ? now() : null;
            $task->save();
            
            // --- Workflow Logic ---
            // If not all tasks in the group are completed, set group to in_progress
            $group = $task->group;
            if ($group) {
                $allTasksCompleted = $group->tasks()->where('status', '!=', 'published')->count() === 0;
                
                if ($allTasksCompleted && $group->status !== 'published') {
                    // All tasks done - keep group as is (user can manually mark as published)
                } elseif (!$allTasksCompleted && $group->status === 'published') {
                    // Not all tasks done but group is published - move to in_progress
                    $group->status = 'in_progress';
                    $group->save();
                }
            }
        }
    }

    public function markGroupComplete($groupId)
    {
        $group = \App\Models\TaskGroup::find($groupId);
        // Use Policy if strict, or basic owner/assignee check (similar to tasks)
        if ($group) {
             $group->status = 'published';
             $group->save();
        }
    }

    public function render()
    {
        // Use new multi-assignee relationship (task_user pivot table)
        $myTasks = Task::where('board_id', $this->boardId)
            ->where(function($query) {
                // Check new multi-assignee relationship
                $query->whereHas('users', function($q) {
                    $q->where('user_id', Auth::id());
                })
                // Fallback to old assignee_id field
                ->orWhere('assignee_id', Auth::id());
            })
            ->with(['tags', 'group' => function ($query) {
                $query->withCount(['tasks', 'tasks as published_tasks_count' => function ($q) {
                    $q->where('status', 'published');
                }]);
            }]) // Eager load tags and parent group with counts
            // Sort by Priority (High -> Medium -> Low -> None) then Due Date
            ->orderByRaw("CASE 
                WHEN priority = 'high' THEN 1 
                WHEN priority = 'medium' THEN 2 
                WHEN priority = 'low' THEN 3 
                ELSE 4 END")
            ->orderBy('due_date') // Sooner due dates first
            ->get();

        return view('livewire.individual', [
            'myTasks' => $myTasks
        ]);
    }
}
