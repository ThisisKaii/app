<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;

class Table extends Component
{
    public $tasks = [];
    public $boardId;

    public function mount($boardId)
    {

        $board = Board::find($boardId);
            $this->tasks = $board->tasks()
                ->where('user_id', auth()->id())
                ->with(['assignee', 'tags'])
                ->orderBy('order')
                ->get();

    }

    public function render()
    {
        return view('livewire.table');
    }
}