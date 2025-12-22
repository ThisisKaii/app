<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

/**
 * Kanban board component for Normal boards displaying task groups and tasks.
 * Handles drag-and-drop ordering and self-assignment functionality.
 */
class Boards extends Component
{
    public $groups;
    public $board;
    public $boardId;

    protected $listeners = ['refreshBoard' => 'loadGroups', 'takeTask' => 'takeTask'];

    /**
     * Check if current user has Owner or Admin role.
     */
    public function getCanEditProperty()
    {
        $member = $this->board->members()->where('user_id', Auth::id())->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    public function mount($boardId)
    {
        $this->board = Board::findOrFail($boardId);
        
        if ($this->board->list_type === 'Business') {
            abort(403, 'This component is for normal boards only.');
        }

        $this->loadGroups();
    }

    public function loadGroups()
    {
        $this->groups = \App\Models\TaskGroup::where('board_id', $this->board->id)
            ->with(['tasks' => function($query) {
                $query->orderBy('order')->with('users');
            }])
            ->orderBy('order')
            ->get();
    }

    public function updateGroupOrder($groupId, $newOrder)
    {
        $group = \App\Models\TaskGroup::find($groupId);
        if ($group) {
            $group->order = $newOrder;
            $group->save();
        }
    }

    public function updateTaskOrder($taskId, $newGroupId, $newOrder)
    {
        $task = Task::find($taskId);
        if ($task) {
            $task->group_id = $newGroupId;
            $task->order = $newOrder;
            $task->save();
        }
    }

    public function updateGroupStatus($groupId, $newStatus, $newOrder)
    {
        $group = \App\Models\TaskGroup::find($groupId);
        if ($group) {
            try {
                \Illuminate\Support\Facades\Gate::authorize('update', $group);
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->dispatch('error', 'You do not have permission to move this card.');
                return;
            }

            $group->status = $newStatus;
            $group->order = $newOrder;
            $group->save();
            
            $this->reorderGroups($newStatus);
            $this->loadGroups();
        }
    }

    private function reorderGroups($status)
    {
        $groups = \App\Models\TaskGroup::where('board_id', $this->board->id)
            ->where('status', $status)
            ->orderBy('order')
            ->get();

        foreach ($groups as $index => $group) {
            $group->order = $index;
            $group->save();
        }
    }

    /**
     * Toggle task assignment for the current user (self-assign/unassign).
     */
    public function takeTask($taskId)
    {
        $task = Task::find($taskId);
        if (!$task) return;
        
        $userId = Auth::id();
        
        if ($task->users()->where('user_id', $userId)->exists()) {
            $task->users()->detach($userId);
        } else {
            $task->users()->attach($userId);
        }
        
        $this->loadGroups();
    }

    public function render()
    {
        return view('livewire.board', ['groups' => $this->groups, 'canEdit' => $this->canEdit]);
    }
}