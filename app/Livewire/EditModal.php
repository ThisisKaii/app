<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;

class EditModal extends Component
{
    public $board;
    public $tasks = [];
    public $tasksId;
    public $title;
    public $type;
    public $priority;
    public $due_date;
    public $description;
    public $url;

    public $status;
    public $isOpen = false;
    public $confirmDelete = false;

    protected $listeners = ['openEditModal' => 'openModal'];
    public function mount($board, $status)
    {
        $this->board = $board;
        $this->tasks = Task::where('board_id', $board->id)->get();
        $this->status = $status;
    }

    public function openModal($taskId)
    {
        $this->tasksId = $taskId;

        // Load task data
        $task = Task::find($taskId);

        if ($task) {
            $this->title = $task->title;
            $this->type = $task->type;
            $this->priority = $task->priority;
            $this->due_date = $task->due_date?->format('Y-m-d');
            $this->description = $task->description;
            $this->url = $task->url;
        }

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['title', 'type', 'priority', 'due_date', 'description', 'url']);
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'url' => 'nullable|url|max:500',
        ]);

        $task = Task::find($this->tasksId);
        $task->update([
            'title' => $this->title,
            'type' => $this->type ?: null,
            'priority' => $this->priority ?: null,
            'due_date' => $this->due_date ?: null,
            'description' => $this->description ?: null,
            'url' => $this->url ?: null,
        ]);
        $this->tasks = Task::where('board_id', $this->board->id)->get();
        $this->closeModal();

        session()->flash('success', 'Task updated successfully!');

        $this->dispatch('task-updated');

        return $this->redirect(route('boards.show', $this->boardId));
    }
    public function askDelete()
    {
        $this->confirmDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmDelete = false;
    }


    public function delete()
    {
        $task = Task::find($this->tasksId);

        if ($task) {
            $task->delete();
        }

        $this->tasks = Task::where('board_id', $this->board->id)->get();
        $this->cancelDelete();

        $this->closeModal();

        session()->flash('success', 'Task deleted successfully!');

        $this->dispatch('task-deleted');

        return $this->redirect(route('boards.show', $this->board->id));
    }


    public function render()
    {
        return view('livewire.edit-modal');
    }
}
