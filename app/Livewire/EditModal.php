<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\Board;
use App\Models\Tag;

/**
 * Modal component for creating and editing tasks within a specific status column.
 * Handles task form validation, tag syncing, and board member assignment.
 */
class EditModal extends Component
{
    public $board, $status, $tasks = [];
    public $showModal = false, $taskId = null;
    public $title = '', $description = '', $type = '', $priority = 'low';
    public $taskStatus = '', $due_date = null, $url = '', $assignee_id = null, $tagsInput = '';

    public $availableColors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e', '#3b82f6', '#6366f1', '#a855f7', '#ec4899'];

    protected $listeners = [
        'open-modal-{status}' => 'openModal',
        'task-updated' => 'loadTasks',
        'refresh-tasks' => 'loadTasks'
    ];

    public function mount(Board $board, $status)
    {
        $this->board = $board;
        $this->status = $status;
        $this->loadTasks();
    }

    /**
     * Refresh the task list for this status column.
     */
    public function loadTasks()
    {
        $this->tasks = Task::where('board_id', $this->board->id)
            ->where('status', $this->status)
            ->with(['assignee', 'tags'])
            ->orderBy('order')
            ->get();
    }

    /**
     * Open the modal for creating or editing a task.
     *
     * @param int|null $taskId Pass null to create a new task
     */
    public function openModal($taskId = null)
    {
        $this->resetForm();

        if ($taskId) {
            $task = Task::with('tags')->find($taskId);
            if (!$task)
                return;

            $this->taskId = $taskId;
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->type = $task->type ?? '';
            $this->priority = $task->priority ?? 'low';
            $this->taskStatus = $task->status;
            $this->due_date = $task->due_date?->format('Y-m-d');
            $this->url = $task->url ?? '';
            $this->assignee_id = $task->assignee_id;
            $this->tagsInput = $task->tags->pluck('name')->implode(', ');
        } else {
            $this->taskStatus = $this->status;
            $this->assignee_id = auth()->id();
        }

        $this->showModal = true;
    }

    public function saveTask()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'taskStatus' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);
        
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'priority' => $this->priority ?: 'low',
            'status' => $this->taskStatus,
            'due_date' => $this->due_date,
            'url' => $this->url,
            'assignee_id' => $this->assignee_id,
        ];

        if ($this->taskId) {
            \Illuminate\Support\Facades\Gate::authorize('updateTask', $this->board);
            $task = Task::find($this->taskId);
            $task->update($data);
        } else {
            \Illuminate\Support\Facades\Gate::authorize('createTask', $this->board);
            $data['board_id'] = $this->board->id;
            $data['user_id'] = auth()->id();
            $data['order'] = Task::where('board_id', $this->board->id)
                ->where('status', $this->taskStatus)
                ->max('order') + 1 ?? 0;

            if (empty($data['assignee_id'])) {
                $data['assignee_id'] = auth()->id();
            }

            $task = Task::create($data);
        }

        if ($this->tagsInput) {
            $tagIds = [];
            foreach (array_filter(array_map('trim', explode(',', $this->tagsInput))) as $name) {
                $tag = Tag::firstOrCreate(['name' => $name], ['color' => $this->availableColors[array_rand($this->availableColors)]]);
                $tagIds[] = $tag->id;
            }
            $task->syncTagsWithLog($tagIds);
        } else {
            $task->tags()->detach();
        }

        $this->closeModal();

        $this->dispatch('task-updated');
        $this->dispatch('refresh-tasks');

        $this->js('setTimeout(() => initDragAndDrop(), 100)');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->loadTasks();
    }

    protected function resetForm()
    {
        $this->taskId = null;
        $this->title = '';
        $this->description = '';
        $this->type = '';
        $this->priority = 'low';
        $this->taskStatus = $this->status;
        $this->due_date = null;
        $this->url = '';
        $this->assignee_id = null;
        $this->tagsInput = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.edit-modal', [
            'users' => $this->board->members()->orderBy('name')->get(),
        ]);
    }
}