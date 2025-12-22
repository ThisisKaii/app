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

use Livewire\WithPagination;

/**
 * Table view component for displaying tasks grouped by task groups with filtering and pagination.
 * Supports task CRUD operations, status toggling, and task assignment.
 */
class TableView extends Component
{
    use WithPagination;

    public $boardId;
    public $board;

    public $statusFilter = '';
    public $priorityFilter = '';
    public $assigneeFilter = '';
    public $searchFilter = '';
    public $showFilters = false;

    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $taskId = null;
    public $deleteTaskId = null;

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

    public $expandedGroups = [];

    public function getGroups()
    {
        try {
            $query = \App\Models\TaskGroup::where('board_id', $this->boardId)
                ->with(['tasks' => function($query) {
                    $query->orderBy('order')->with('users', 'tags');
                    
                    if ($this->priorityFilter) {
                        $query->where('priority', $this->priorityFilter);
                    }
                    if ($this->assigneeFilter) {
                        $query->whereHas('users', function ($q) {
                            $q->where('name', $this->assigneeFilter);
                        });
                    }
                    if ($this->searchFilter) {
                        $query->where(function($q) {
                            $q->where('title', 'like', '%' . $this->searchFilter . '%')
                              ->orWhere('type', 'like', '%' . $this->searchFilter . '%');
                        });
                    }
                }])
                ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                    $query->whereNotNull('completed_at');
                }])
                ->orderBy('order');

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            if ($this->priorityFilter) {
                $query->whereHas('tasks', function($q) {
                    $q->where('priority', $this->priorityFilter);
                });
            }

            if ($this->assigneeFilter) {
                $query->whereHas('tasks.users', function ($q) {
                    $q->where('name', $this->assigneeFilter);
                });
            }

            if ($this->searchFilter) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->searchFilter . '%')
                      ->orWhereHas('tasks', function($t) {
                          $t->where('title', 'like', '%' . $this->searchFilter . '%')
                            ->orWhere('type', 'like', '%' . $this->searchFilter . '%');
                      });
                });
            }

            return $query->paginate(10);

        } catch (\Exception $e) {
            Log::error('TableView Error: ' . $e->getMessage());
            return \App\Models\TaskGroup::where('id', -1)->paginate(10);
        }
    }

    public function toggleGroup($groupId)
    {
        if (in_array($groupId, $this->expandedGroups)) {
            $this->expandedGroups = array_diff($this->expandedGroups, [$groupId]);
        } else {
            $this->expandedGroups[] = $groupId;
        }
    }

    public function getUsers()
    {
        return $this->board->members()->orderBy('name')->get();
    }

    public function paginationView()
    {
        return 'livewire.pagination-dark';
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

    public function toggleTaskCompletion($taskId)
    {
        try {
            $task = Task::with(['group', 'users'])->find($taskId);
            if (!$task) return;

            // Permission check
            $userId = auth()->id();
            $member = $this->board->members()->where('user_id', $userId)->first();
            
            if (!$member) {
                return; // Not a board member
            }
            
            $isOwnerOrAdmin = in_array($member->pivot->role, ['owner', 'admin']);
            $isAssignedToMe = $task->assignee_id == $userId || 
                              $task->users->contains('id', $userId);
            
            // Owner/Admin can toggle any task, members can only toggle their assigned tasks
            if (!$isOwnerOrAdmin && !$isAssignedToMe) {
                return; // Members can't toggle unassigned tasks
            }

            $previousStatus = $task->status;
            $newStatus = $previousStatus === 'published' ? 'in_progress' : 'published';
            
            $task->status = $newStatus;
            $task->completed_at = $newStatus === 'published' ? now() : null;
            $task->save();

            $group = $task->group;
            if ($group) {
                $allTasksCompleted = $group->tasks()->where('status', '!=', 'published')->count() === 0;
                
                if (!$allTasksCompleted && $group->status === 'published') {
                    $group->status = 'in_progress';
                    $group->save();
                }
            }

            $this->dispatch('taskSaved'); // Trigger refresh
        } catch (\Exception $e) {
            Log::error('Task completion toggle error: ' . $e->getMessage());
        }
    }

    /**
     * Validate and save task form data.
     */
    public function saveTask()
    {
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

                session()->flash('success', 'Task updated successfully!');
            } else {
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

                session()->flash('success', 'Task created successfully!');
            }

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

            session()->flash('success', 'Task deleted successfully!');

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

    /**
     * Check if current user has Owner or Admin role.
     */
    public function getCanEditProperty()
    {
        $member = $this->board->members()->where('user_id', auth()->id())->first();
        return $member && in_array($member->pivot->role, ['owner', 'admin']);
    }

    // Allow members to take (assign to themselves) a task
    public function takeTask($taskId)
    {
        $task = Task::find($taskId);
        if (!$task) return;
        
        $userId = auth()->id();
        
        if ($task->users()->where('user_id', $userId)->exists()) {
            $task->users()->detach($userId);
        } else {
            $task->users()->attach($userId);
        }
        
        $this->dispatch('taskSaved');
    }

    public function render()
    {
        return view('livewire.table-view', [
            'groups' => $this->getGroups(),
            'users' => $this->getUsers(),
            'canEdit' => $this->canEdit,
        ]);
    }
}