<?php

namespace App\Livewire;

use Laravel\Pail\ValueObjects\Origin\Console;
use Livewire\Component;
use App\Models\Board;

class BoardList extends Component
{
    public $boards;
    public $boardId;
    protected $listeners = [
        "boardAdded" => "refreshBoards"
    ];

    public function mount()
    {
        $this->boards = Board::where('user_id', auth()->id())->get();
    }

    public function refreshBoards($boardId)
    {

        $this->boards = Board::where('user_id', auth()->id())->get();
    }
    public function render()
    {
        return view('livewire.board-list');
    }
}
