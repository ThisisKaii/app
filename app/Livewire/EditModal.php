<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\Board;
use App\Models\Tag;

class EditModal extends Component
{
    public $board, $status, $tasks = [];
    public $showModal = false, $taskId = null;
    public $title = '', $description = '', $type = '', $priority = '';
    public $taskStatus = '', $due_date = null, $url = '', $assignee_id = null, $tagsInput = '';

    public $availableColors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e', '#3b82f6', '#6366f1', '#a855f7', '#ec4899'];

    // Listen for events from both this status and global task updates
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

    public function loadTasks()
    {
        $this->tasks = Task::where('board_id', $this->board->id)
            ->where('status', $this->status)
            ->with(['assignee', 'tags'])
            ->orderBy('order')
            ->get();
    }

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
            $this->priority = $task->priority ?? '';
            $this->taskStatus = $task->status;
            $this->due_date = $task->due_date?->format('Y-m-d');
            $this->url = $task->url ?? '';
            $this->assignee_id = $task->assignee_id;
            $this->tagsInput = $task->tags->pluck('name')->implode(', ');
        } else {
            $this->taskStatus = $this->status;
        }

        $this->showModal = true;
    }

    public function saveTask()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'taskStatus' => 'required|string',
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'priority' => $this->priority,
            'status' => $this->taskStatus,
            'due_date' => $this->due_date,
            'url' => $this->url,
            'assignee_id' => $this->assignee_id,
        ];

        if ($this->taskId) {
            $task = Task::find($this->taskId);
            $task->update($data);
        } else {
            $data['board_id'] = $this->board->id;
            $data['user_id'] = auth()->id();
            $data['order'] = Task::where('board_id', $this->board->id)
                ->where('status', $this->taskStatus)
                ->max('order') + 1 ?? 0;
            $task = Task::create($data);
        }

        // Handle tags
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

        // Refresh all columns
        $this->dispatch('task-updated');
        $this->dispatch('refresh-tasks');

        // Reinitialize drag and drop after DOM update
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
        $this->priority = '';
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