<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use Illuminate\Support\Facades\Gate;

class TasksList extends Component
{
    public $boardId;
    public $view = 'individual';
    public $hasBoardMembers = false;

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->checkBoardMembers();
    }

    public function checkBoardMembers()
    {
        $board = Board::find($this->boardId);
        
        if (!$board) {
            $this->hasBoardMembers = false;
            return;
        }

        // Check if board has more than just the creator
        $this->hasBoardMembers = $board->members()->count() > 1;
    }

    public function switchView($viewName)
    {
        // If trying to switch to teams view but no members, stay on individual
        if ($viewName === 'teams' && !$this->hasBoardMembers) {
            $this->dispatch('showToast', 
                message: 'Add team members to view team overview', 
                type: 'error'
            );
            return;
        }

        $this->view = $viewName;
    }

    public function render()
    {
        return view('livewire.tasks-list');
    }
}