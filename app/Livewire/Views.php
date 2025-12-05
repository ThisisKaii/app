<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class Views extends Component
{
    public $view;
    public $boardId;
    public $renderKey = 0;

    public function mount($board)
    {
        $this->view = 'board';
        $this->boardId = $board->id;
    }

    #[On('board-change')]
    public function changeView($viewName)
    {
        \Log::info('Changing view to: ' . $viewName);
        
        // Only change view if it's different from current view
        if ($this->view !== $viewName) {
            $this->view = $viewName;
            $this->renderKey++;
            
            // Force Livewire to re-render
            $this->dispatch('$refresh');
        } else {
            // If same view, do nothing - prevents the wire:key bug
            \Log::info('Same view clicked, ignoring');
        }
    }

    public function render()
    {
        \Log::info('Current view: ' . $this->view . ', renderKey: ' . $this->renderKey);
        return view('livewire.views');
    }
}