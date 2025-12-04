<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;

class Boards extends Component
{
    public $board;
    public $boardId;

    public function mount($boardId)
    {
        $this->board = Board::findOrFail($boardId);
    }

    public function render()
    {
        return view('livewire.board');
    }
}