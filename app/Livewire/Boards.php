<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Task;
class Boards extends Component
{
    public $board;
    public $boardId;
    public $task;
    public function mount($boardId)
    {
        $this->board = Board::findOrFail($boardId);
        $this->task = Task::where('board_id', $boardId)->get();
    }

    public function render()
    {
        return view('livewire.board');
    }
}