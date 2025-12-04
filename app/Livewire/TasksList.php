<?php

namespace App\Livewire;

use Livewire\Component;

class TasksList extends Component
{
    public $view = 'individual';
    public $boardId;

    public function mount($boardId = null)
    {
        $this->boardId = $boardId;
    }

    public function switchView($view)
    {
        $this->view = $view;
    }

    public function render()
    {
        return view('livewire.tasks-list');
    }
}