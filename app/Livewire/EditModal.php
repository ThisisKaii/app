<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;

class EditModal extends Component
{
    public $board;
    public $boardId;
    public $tasks = [];
    public $status;

    protected $listeners = [
        'task-updated' => 'refreshTasks'
    ];

    public function mount($board, $status)
    {
        $this->board = $board;
        $this->boardId = $board->id;
        $this->status = $status;
        $this->refreshTasks();
    }

    public function refreshTasks()
    {
        $this->tasks = Task::where('board_id', $this->boardId)
            ->where('status', $this->status)
            ->get();
    }

    public function render()
    {
        return view('livewire.edit-modal');
    }
}