<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\User;
use App\Models\Tag;

class addTable extends Component
{
    public $show = false;
    public $isEditing = false;
    public $taskId = null;
    public $boardId;
    
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
    
    // Delete confirmation
    public $showDeleteConfirm = false;
    
    // Available users for assignment
    public $users = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'nullable|string|max:50',
        'priority' => 'nullable|in:low,medium,high',
        'status' => 'required|in:to_do,in_progress,in_review,completed',
        'due_date' => 'nullable|date',
        'url' => 'nullable|url|max:255',
        'assignee_id' => 'nullable|exists:users,id',
        'tagsInput' => 'nullable|string',
    ];

    protected $listeners = [
        'openTaskModal' => 'open',
    ];

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->users = User::orderBy('name')->get();
    }

    public function open($taskId = null)
    {
        $this->resetForm();
        $this->show = true;
        
        if ($taskId) {
            $this->isEditing = true;
            $this->taskId = $taskId;
            $this->loadTask($taskId);
        } else {
            $this->isEditing = false;
            $this->taskId = null;
        }
    }

    public function loadTask($taskId)
    {
        $task = Task::with('tags')->findOrFail($taskId);
        
        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->type = $task->type ?? '';
        $this->priority = $task->priority ?? '';
        $this->status = $task->status;
        $this->due_date = $task->due_date;
        $this->url = $task->url ?? '';
        $this->assignee_id = $task->assignee_id;
        
        // Load tags as comma-separated string
        if ($task->tags->isNotEmpty()) {
            $this->tagsInput = $task->tags->pluck('name')->implode(', ');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEditing) {
                $task = Task::findOrFail($this->taskId);
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
                
                $message = 'Task updated successfully!';
            } else {
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
                    'order' => Task::where('board_id', $this->boardId)->max('order') + 1,
                ]);
                
                $message = 'Task created successfully!';
            }

            // Handle tags
            $this->syncTags($task);

            $this->closeModal();
            $this->dispatch('taskSaved');
            $this->dispatch('showToast', message: $message, type: 'success');
            
        } catch (\Exception $e) {
            \Log::error('Task save error: ' . $e->getMessage());
            $this->dispatch('showToast', message: 'Failed to save task', type: 'error');
        }
    }

    protected function syncTags($task)
    {
        if (empty($this->tagsInput)) {
            $task->tags()->detach();
            return;
        }

        // Parse tags from comma-separated input
        $tagNames = array_filter(array_map('trim', explode(',', $this->tagsInput)));
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            // Find or create tag
            $tag = Tag::firstOrCreate(
                ['name' => $tagName],
                ['user_id' => auth()->id()]
            );
            $tagIds[] = $tag->id;
        }

        // Sync tags to task
        $task->tags()->sync($tagIds);
    }

    public function askDelete()
    {
        $this->showDeleteConfirm = true;
    }

    public function delete()
    {
        try {
            $task = Task::findOrFail($this->taskId);
            $task->delete();

            $this->showDeleteConfirm = false;
            $this->closeModal();
            $this->dispatch('taskSaved');
            $this->dispatch('showToast', message: 'Task deleted successfully!', type: 'success');
            
        } catch (\Exception $e) {
            \Log::error('Task delete error: ' . $e->getMessage());
            $this->dispatch('showToast', message: 'Failed to delete task', type: 'error');
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirm = false;
    }

    public function closeModal()
    {
        $this->show = false;
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
        $this->showDeleteConfirm = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.add-table');
    }
}