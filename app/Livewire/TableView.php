<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use App\Models\Tag;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class TableView extends Component
{
    public $boardId;
    public $board;

    // Filter properties
    public $statusFilter = '';
    public $priorityFilter = '';
    public $assigneeFilter = '';
    public $searchFilter = '';
    public $showFilters = false;

    // Modal properties
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $taskId = null;
    public $deleteTaskId = null;

    // Form fields
    public $title = '';
    public $description = '';
    public $type = '';
    public $priority = '';
    public $status = 'to_do';
    public $due_date = null;
    public $url = '';
    public $assignee_id = null;
    public $tagsInput = '';

    public $availableColors = [
        '#ef4444',
        '#f59e0b',
        '#eab308',
        '#22c55e',
        '#3b82f6',
        '#6366f1',
        '#a855f7',
        '#ec4899',
    ];

    protected $listeners = [
        'taskSaved' => '$refresh',
        'taskDeleted' => '$refresh'
    ];

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->board = Board::findOrFail($boardId);
    }

    public function getTasks()
    {
        try {
            $query = Task::where('board_id', $this->boardId)
                ->with(['assignee', 'tags'])
                ->orderBy('order');

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            if ($this->priorityFilter) {
                $query->where('priority', $this->priorityFilter);
            }

            if ($this->assigneeFilter) {
                $query->whereHas('assignee', function ($q) {
                    $q->where('name', $this->assigneeFilter);
                });
            }

            if ($this->searchFilter) {
                $query->where('title', 'like', '%' . $this->searchFilter . '%');
            }

            return $query->get();

        } catch (\Exception $e) {
            Log::error('TableView Error: ' . $e->getMessage());
            return collect([]);
        }
    }

    public function getUsers()
    {
        return $this->board->members()->orderBy('name')->get();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->statusFilter = '';
        $this->priorityFilter = '';
        $this->assigneeFilter = '';
        $this->searchFilter = '';
    }

    public function openTaskModal($taskId = null)
    {
        $this->resetForm();
        $this->resetValidation();

        if ($taskId) {
            $task = Task::with('tags')->find($taskId);

            if (!$task) {
                session()->flash('error', 'Task not found');
                return;
            }

            // Check authorization for viewing/editing
            if (!Gate::allows('viewTasks', $this->board)) {
                session()->flash('error', 'You are not authorized to view this task.');
                return;
            }

            $this->isEditing = true;
            $this->taskId = $taskId;
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->type = $task->type ?? '';
            $this->priority = $task->priority ?? '';
            $this->status = $task->status;
            $this->due_date = $task->due_date ? $task->due_date->format('Y-m-d') : null;
            $this->url = $task->url ?? '';
            $this->assignee_id = $task->assignee_id;

            if ($task->tags->count() > 0) {
                $this->tagsInput = $task->tags->pluck('name')->implode(', ');
            }
        } else {
            $this->isEditing = false;
            $this->taskId = null;
        }

        $this->showModal = true;
    }

    public function saveTask()
    {
        Log::info('saveTask called', [
            'isEditing' => $this->isEditing,
            'taskId' => $this->taskId,
            'title' => $this->title,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date
        ]);

        // Validate
        $this->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:to_do,in_progress,in_review,published',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'url' => 'nullable|url',
        ]);

        try {
            // Convert empty strings to null
            $this->due_date = $this->due_date ?: null;
            $this->assignee_id = $this->assignee_id ?: null;
            $this->priority = $this->priority ?: null;
            $this->type = $this->type ?: null;
            $this->url = $this->url ?: null;

            if ($this->isEditing) {
                // Check authorization for updating
                if (!Gate::allows('updateTask', $this->board)) {
                    session()->flash('error', 'You are not authorized to update this task.');
                    return;
                }

                $task = Task::find($this->taskId);

                if (!$task) {
                    session()->flash('error', 'Task not found');
                    $this->closeModal();
                    return;
                }

                Log::info('Updating task', [
                    'task_id' => $task->id,
                    'data' => [
                        'title' => $this->title,
                        'description' => $this->description,
                        'type' => $this->type,
                        'priority' => $this->priority,
                        'status' => $this->status,
                        'due_date' => $this->due_date,
                        'url' => $this->url,
                        'assignee_id' => $this->assignee_id,
                    ]
                ]);

                $task->update([
                    'title' => $this->title,
                    'description' => $this->description,
                    'type' => $this->type,
                    'priority' => $this->priority,
                    'status' => $this->status,
                    'due_date' => $this->due_date,
                    'url' => $this->url,
                    'assignee_id' => $this->assignee_id,
                ]);

                $this->syncTags($task);


                Log::info('Task updated successfully', ['task_id' => $task->id]);
                session()->flash('success', 'Task updated successfully!');
            } else {
                // Check authorization for creating
                if (!Gate::allows('createTask', $this->board)) {
                    session()->flash('error', 'You are not authorized to create tasks.');
                    return;
                }

                $maxOrder = Task::where('board_id', $this->boardId)
                    ->where('status', $this->status)
                    ->max('order') ?? -1;

                $task = Task::create([
                    'title' => $this->title,
                    'description' => $this->description,
                    'type' => $this->type,
                    'priority' => $this->priority,
                    'status' => $this->status,
                    'due_date' => $this->due_date,
                    'url' => $this->url,
                    'board_id' => $this->boardId,
                    'user_id' => auth()->id(),
                    'assignee_id' => $this->assignee_id,
                    'order' => $maxOrder + 1,
                ]);

                $this->syncTags($task);


                Log::info('Task created successfully', ['task_id' => $task->id]);
                session()->flash('success', 'Task created successfully!');
            }

            // Close modal and reset
            $this->closeModal();

        } catch (\Exception $e) {
            Log::error('Task save error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to save task: ' . $e->getMessage());
        }
    }

    protected function syncTags($task)
    {
        if (empty($this->tagsInput)) {
            $task->tags()->detach();
            return;
        }

        $tagNames = array_filter(array_map('trim', explode(',', $this->tagsInput)));
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(
                ['name' => $tagName],
                ['color' => $this->availableColors[array_rand($this->availableColors)]]
            );
            $tagIds[] = $tag->id;
        }

        $task->tags()->sync($tagIds);
    }

    public function showDeleteConfirmation($taskId = null)
    {
        $this->deleteTaskId = $taskId ?? $this->taskId;

        if ($this->showModal) {
            $this->showModal = false;
            $this->resetForm();
        }

        $this->showDeleteModal = true;
    }

    public function performDelete()
    {
        try {
            // Check authorization
            if (!Gate::allows('deleteTask', $this->board)) {
                session()->flash('error', 'You are not authorized to delete this task.');
                return;
            }

            $task = Task::find($this->deleteTaskId);

            if (!$task) {
                session()->flash('error', 'Task not found');
                $this->closeDeleteModal();
                return;
            }

            $taskTitle = $task->title;
            $boardId = $task->board_id;

            $task->delete();

            Log::info('Task deleted successfully', ['task_id' => $this->deleteTaskId]);
            session()->flash('success', 'Task deleted successfully!');

            // Close modal
            $this->closeDeleteModal();

        } catch (\Exception $e) {
            Log::error('Task deletion error: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete task: ' . $e->getMessage());
            $this->closeDeleteModal();
        }
    }

    public function cancelDelete()
    {
        $this->closeDeleteModal();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteTaskId = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->type = '';
        $this->priority = '';
        $this->status = 'to_do';
        $this->due_date = null;
        $this->url = '';
        $this->assignee_id = auth()->id();
        $this->tagsInput = '';
        $this->isEditing = false;
        $this->taskId = null;
    }

    public function render()
    {
        return view('livewire.table-view', [
            'tasks' => $this->getTasks(),
            'users' => $this->getUsers(),
        ]);
    }
}