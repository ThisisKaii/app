<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Task;
use App\Models\ActivityLog;

class AddModal extends Component
{
    // Modal state
    public $show = false;
    public $showDeleteConfirm = false;

    // Component props
    public $boardId;
    public $status; // Column status (for new group)

    // Mode: 'group' or 'task'
    public $mode = 'task';
    public $groupId = null;
    public $entityId = null; // ID of the group or task being edited
    public $isEditing = false;

    // Form fields
    public $title = ''; // For Group Title
    public $type = ''; // For Task Type
    public $priority = 'low';
    public $due_date = '';
    public $url = '';
    public $assignee_ids = []; // Array for multiple assignees

    public function mount($boardId, $status = 'to_do')
    {
        $this->boardId = $boardId;
        $this->status = $status;
    }

    public function getListeners()
    {
        return [
            'open-group-modal' => 'openGroupModal',
            'open-task-modal' => 'openTaskModal',
        ];
    }

    public function getUsersProperty()
    {
        $board = \App\Models\Board::find($this->boardId);
        return $board ? $board->members()->orderBy('name')->get() : collect([]);
    }

    public function getCanAssignProperty()
    {
        $board = \App\Models\Board::find($this->boardId);
        if (!$board) return false;
        
        $member = $board->members()->where('user_id', auth()->id())->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    public function openGroupModal($status = null, $groupId = null)
    {
        $this->resetForm();
        $this->mode = 'group';
        
        if ($groupId) {
            $this->loadGroup($groupId);
        } else {
            $this->status = $status ?? 'to_do';
        }
        
        $this->show = true;
    }

    public function openTaskModal($taskId = null, $groupId = null)
    {
        $this->resetForm();
        $this->mode = 'task';

        if ($taskId) {
            $this->loadTask($taskId);
        } elseif ($groupId) {
            $this->groupId = $groupId;
            $this->assignee_ids = [auth()->id()];
        }
        
        $this->show = true;
    }

    private function loadGroup($groupId)
    {
        $group = \App\Models\TaskGroup::find($groupId);
        if (!$group) return;

        $this->entityId = $group->id;
        $this->isEditing = true;
        $this->title = $group->title;
        $this->status = $group->status;
    }

    private function loadTask($taskId)
    {
        $task = Task::with('users')->find($taskId);
        if (!$task) return;

        $this->entityId = $task->id;
        $this->isEditing = true;
        $this->groupId = $task->group_id;
        $this->type = $task->type ?? '';
        $this->priority = $task->priority ?? 'low';
        $this->due_date = $task->due_date ? $task->due_date->format('Y-m-d') : '';
        $this->assignee_ids = $task->users->pluck('id')->toArray();
    }

    public function save()
    {
        if ($this->mode === 'group') {
            if (!$this->saveGroup()) {
                return;
            }
        } else {
            $this->saveTask();
        }

        $this->dispatch('refreshBoard');
        $this->closeModal();
    }

    private function saveGroup()
    {
        $this->validate(['title' => 'required|string|max:255']);

        // Check for duplicate title in this board (application level uniqueness)
        $exists = \App\Models\TaskGroup::where('board_id', $this->boardId)
            ->where('title', $this->title)
            ->when($this->isEditing && $this->entityId, function($q) {
                return $q->where('id', '!=', $this->entityId);
            })
            ->exists();

        if ($exists) {
            $this->addError('title', 'A group with this title already exists on this board.');
            return false;
        }

        if ($this->isEditing && $this->entityId) {
            \App\Models\TaskGroup::find($this->entityId)->update([
                'title' => $this->title
            ]);
        } else {
            \App\Models\TaskGroup::create([
                'board_id' => $this->boardId,
                'title' => $this->title,
                'status' => $this->status,
                'order' => \App\Models\TaskGroup::where('board_id', $this->boardId)->where('status', $this->status)->max('order') + 1
            ]);
        }
        
        return true;
    }

    private function saveTask()
    {
        $this->validate([
            'type' => 'nullable|string|max:50', // Type is effectively the title now
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assignee_ids' => 'array'
        ]);
        
        // If type is empty, maybe require it? User said "type are like sub category".
        if (empty($this->type)) $this->type = 'Task'; 
        
        // Determine status from Group
        $groupStatus = 'to_do';
        if ($this->groupId) {
            $group = \App\Models\TaskGroup::find($this->groupId);
            if ($group) {
                $groupStatus = $group->status;
            }
        }

        $data = [
            'type' => $this->type,
            'priority' => $this->priority,
            'due_date' => $this->due_date ?: null,
        ];

        if ($this->isEditing && $this->entityId) {
            $task = Task::find($this->entityId);
            $task->update($data);

            ActivityLog::log(
                $this->boardId,
                Task::class,
                $task->id,
                'update',
                "Updated task details"
            );
        } else {
            $task = Task::create(array_merge($data, [
                'board_id' => $this->boardId,
                'group_id' => $this->groupId,
                'user_id' => auth()->id(),
                'status' => $groupStatus, // Use group status instead of hardcoded 'to_do'
                // Default props
                'title' => $this->type, // Legacy title field
                'order' => Task::where('group_id', $this->groupId)->max('order') + 1
            ]));

            ActivityLog::log(
                $this->boardId,
                Task::class,
                $task->id,
                'create',
                "Created task '{$task->title}'"
            );
        }

        // Sync users
        $task->users()->sync($this->assignee_ids);
    }

    public function askDelete()
    {
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirm = false;
    }

    public function delete()
    {
        if ($this->entityId) {
            if ($this->mode === 'group') {
                \App\Models\TaskGroup::find($this->entityId)->delete();
            } else {
                $task = Task::find($this->entityId);
                ActivityLog::log(
                    $this->boardId,
                    Task::class,
                    $task->id,
                    'delete',
                    "Deleted task '{$task->title}'"
                );
                $task->delete();
            }
            $this->dispatch('refreshBoard');
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->show = false;
        $this->showDeleteConfirm = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->title = '';
        $this->type = '';
        $this->priority = 'low';
        $this->due_date = '';
        $this->assignee_ids = [];
        $this->entityId = null;
        $this->isEditing = false;
        $this->groupId = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.add-modal');
    }
}