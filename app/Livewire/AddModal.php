<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;

class AddModal extends Component
{
    public $isOpen = false;
    public $boardId;
    public $status;
    public $task;
    public $title = '';
    public $type = '';
    public $priority = '';
    public $due_date = '';
    public $description = '';
    public $url = '';

    public function mount($boardId, $status)
    {
        $this->boardId = $boardId;
        $this->status = $status;
    }
    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['title', 'type', 'priority', 'due_date', 'description', 'url']);;
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'url' => 'nullable|url|max:500',
        ]);

        $maxOrder = Task::where('board_id', $this->boardId)
            ->where('status', $this->status)
            ->where('user_id', auth()->id())
            ->max('order') ?? -1;

        Task::create([
            'board_id' => $this->boardId,
            'user_id' => auth()->id(),
            'title' => $this->title,
            'status' => $this->status,
            'type' => $this->type ?: null,
            'priority' => $this->priority ?: null,
            'due_date' => $this->due_date ?: null,
            'description' => $this->description ?: null,
            'url' => $this->url,
            'order' => $maxOrder + 1,

        ]);

        $this->closeModal();

        session()->flash('success', 'Task added successfully!');
        $this->dispatch('task-created');

        return $this->redirect(route('boards.show', $this->boardId));
    }
    public function render()
    {
        return view('livewire.add-modal');
    }
}
