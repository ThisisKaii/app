<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;

class Views extends Component
{
    public $board;
    public $currentView = 'board';

    protected $listeners = ['board-change' => 'changeView'];

    public function mount(Board $board)
    {
        $this->board = $board;

        // Set default view based on board type (with capitalized values)
        if ($board->list_type === 'Business') {
            $this->currentView = 'budget'; // Default to budget view for business boards
        } else {
            $this->currentView = 'board'; // Default to kanban board for normal boards
        }
    }

    public function changeView($viewName)
    {
        // Validate view based on board type (with capitalized values)
        if ($this->board->list_type === 'Business') {
            // Business boards can access: budget, table, members, logs
            $allowedViews = ['budget', 'table', 'members', 'logs'];
        } else {
            // Normal boards can access: board, table, tasks, members, logs
            $allowedViews = ['board', 'table', 'tasks', 'members', 'logs'];
        }

        if (in_array($viewName, $allowedViews)) {
            $this->currentView = $viewName;
        }
    }

    public function render()
    {
        return view('livewire.views');
    }
}