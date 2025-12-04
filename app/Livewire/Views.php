<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class Views extends Component
{
    public $view;
    public $boardId;
    public $renderKey = 0; // Add this line!

    public function mount($board)
    {
        $this->view = 'board';
        $this->boardId = $board->id;
    }

    #[On('board-change')]
    public function changeView($viewName)
    {
        \Log::info('Changing view to: ' . $viewName);
        $this->view = $viewName;
        $this->renderKey++; // Increment to force re-render
    }

    public function render()
    {
        \Log::info('Current view: ' . $this->view);
        return view('livewire.views');
    }
}