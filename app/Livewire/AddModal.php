<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Task;

class AddModal extends Component
{
    // Modal state
    public $show = false;
    public $showDeleteConfirm = false;

    // Component props
    public $boardId;
    public $status;

    // Task data
    public $taskId;
    public $isEditing = false;

    // Form fields
    public $title = '';
    public $type = '';
    public $priority = '';
    public $due_date = '';
    public $url = '';


    public function mount($boardId, $status)
    {
        $this->boardId = $boardId;
        $this->status = $status;
    }

    public function getListeners()
    {
        return [
            "open-modal-{$this->status}" => 'openModal',
        ];
    }

    public function openModal($taskId = null)
    {
        // Prevent multiple opens
        if ($this->show) {
            return;
        }

        // Reset form
        $this->title = '';
        $this->type = '';
        $this->priority = '';
        $this->due_date = '';

        $this->taskId = null;
        $this->isEditing = false;
        $this->showDeleteConfirm = false;

        $this->resetValidation();

        // Load task if editing
        if ($taskId) {
            $this->loadTask($taskId);
        }

        $this->show = true;
    }

    private function loadTask($taskId)
    {
        $task = Task::find($taskId);

        if (!$task) {
            return;
        }

        $this->taskId = $task->id;
        $this->isEditing = true;
        $this->title = $task->title;
        $this->type = $task->type ?? '';
        $this->priority = $task->priority ?? '';
        $this->due_date = $task->due_date ? $task->due_date->format('Y-m-d') : '';
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        // Convert empty fields to null
        if (empty($validated['due_date'])) {
            $validated['due_date'] = null;
        }
        if (empty($validated['priority'])) {
            $validated['priority'] = null;
        }
        if (empty($validated['type'])) {
            $validated['type'] = null;
        }

        if ($this->isEditing && $this->taskId) {
            Task::findOrFail($this->taskId)->update(array_merge($validated, [
                'description' => null,
                'url' => null,
            ]));
        } else {
            Task::create(array_merge($validated, [
                'board_id' => $this->boardId,
                'status' => $this->status,
                'user_id' => auth()->id(),
                'description' => null,
                'url' => null,
            ]));
        }

        $this->dispatch('task-updated');
        $this->closeModal();
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
        if ($this->taskId) {
            Task::find($this->taskId)->delete();
            $this->dispatch('task-updated');
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->show = false;
        $this->showDeleteConfirm = false;

        // Reset all form fields
        $this->title = '';
        $this->type = '';
        $this->priority = '';
        $this->due_date = '';

        $this->taskId = null;
        $this->isEditing = false;

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.add-modal');
    }
}