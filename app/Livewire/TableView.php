<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use App\Models\Tag;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;

class TableView extends Component
{
    public $boardId;
    public $board;
    public $tasks = [];

    // Filter properties
    public $statusFilter = '';
    public $priorityFilter = '';
    public $assigneeFilter = '';
    public $searchFilter = '';
    public $showFilters = false;

    // Modal properties
    public $showModal = false;
    public $showDeleteConfirm = false;
    public $isEditing = false;
    public $taskId = null;

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

    public $users = [];
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

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->board = Board::findOrFail($boardId);
        $this->loadTasks();
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->users = $this->board->members()->orderBy('name')->get();
    }

    public function loadTasks()
    {
        try {
            $board = Board::with([
                'tasks' => function ($query) {
                    $query->with(['assignee', 'tags'])->orderBy('order');
                }
            ])->find($this->boardId);

            if ($board && $board->tasks) {
                $this->tasks = $this->applyFilters($board->tasks);
            } else {
                $this->tasks = collect([]);
            }
        } catch (\Exception $e) {
            \Log::error('TableView Error: ' . $e->getMessage());
            $this->tasks = collect([]);
        }
    }

    public function applyFilters($tasks)
    {
        $filtered = $tasks;

        if ($this->statusFilter) {
            $filtered = $filtered->filter(fn($task) => $task->status === $this->statusFilter);
        }

        if ($this->priorityFilter) {
            $filtered = $filtered->filter(fn($task) => strtolower($task->priority ?? '') === strtolower($this->priorityFilter));
        }

        if ($this->assigneeFilter) {
            $filtered = $filtered->filter(fn($task) => $task->assignee && $task->assignee->name === $this->assigneeFilter);
        }

        if ($this->searchFilter) {
            $filtered = $filtered->filter(fn($task) => stripos($task->title, $this->searchFilter) !== false);
        }

        return $filtered;
    }

    public function updated($property)
    {
        if (in_array($property, ['statusFilter', 'priorityFilter', 'assigneeFilter', 'searchFilter'])) {
            $this->loadTasks();
        }
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
        $this->loadTasks();
    }

    public function openModal($taskId = null)
    {
        $this->resetForm();

        if ($taskId) {
            $this->loadTask($taskId);
        }

        $this->showModal = true;
    }

    public function loadTask($taskId)
    {
        $task = Task::with('tags')->find($taskId);

        if (!$task)
            return;

        $this->isEditing = true;
        $this->taskId = $taskId;
        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->type = $task->type ?? '';
        $this->priority = $task->priority ?? '';
        $this->status = $task->status;
        $this->due_date = $task->due_date;
        $this->url = $task->url ?? '';
        $this->assignee_id = $task->assignee_id;

        if ($task->tags->count() > 0) {
            $this->tagsInput = $task->tags->pluck('name')->implode(', ');
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'status' => 'required',
        ]);

        try {
            if ($this->isEditing) {
                $task = Task::find($this->taskId);

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

                ActivityLog::log(
                    $task->board_id,
                    'Task',
                    $task->id,
                    'updated',
                    auth()->user()->name . ' updated task "' . $task->title . '"'
                );

                session()->flash('success', 'Task updated successfully!');
            } else {
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

                ActivityLog::log(
                    $this->boardId,
                    'Task',
                    $task->id,
                    'created',
                    auth()->user()->name . ' created task "' . $task->title . '"'
                );

                session()->flash('success', 'Task created successfully!');
            }

            $this->closeModal();
            $this->loadTasks();

        } catch (\Exception $e) {
            \Log::error('Task save error: ' . $e->getMessage());
            session()->flash('error', 'Failed to save task');
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

    public function confirmDelete()
    {
        $this->showDeleteConfirm = true;
    }

    public function deleteTask()
    {
        try {
            $task = Task::find($this->taskId);

            if ($task) {
                ActivityLog::log(
                    $this->boardId,
                    'Task',
                    null,
                    'deleted',
                    auth()->user()->name . ' deleted task "' . $task->title . '"'
                );

                $task->delete();
            }

            $this->showDeleteConfirm = false;
            $this->closeModal();
            $this->loadTasks();

            session()->flash('success', 'Task deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Task delete error: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete task');
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirm = false;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteConfirm = false;
        $this->resetForm();
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
        $this->assignee_id = null;
        $this->tagsInput = '';
        $this->isEditing = false;
        $this->taskId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.table-view');
    }
}