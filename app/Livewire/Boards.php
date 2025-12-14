<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Task;

class Boards extends Component
{
    public $groups;
    public $board;
    public $boardId;

    protected $listeners = ['refreshBoard' => 'loadGroups'];

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
            $group->status = $newStatus;
            $group->order = $newOrder;
            $group->save();
            
            // Reorder other groups in the new status
            $this->reorderGroups($newStatus);
            
            $this->loadGroups(); // Refresh
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

    public function render()
    {
        return view('livewire.board', ['groups' => $this->groups]);
    }
}