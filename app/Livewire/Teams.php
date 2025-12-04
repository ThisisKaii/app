<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Task;
use Livewire\Component;

class Teams extends Component
{

    public $teamMembers;
    public $unassignedTasks;
    
    public function mount()
    {
        $this->teamMembers = User::with('tasks')->get();
        $this->unassignedTasks = Task::whereNull('assignee_id')->get();

    }
    public function render()
    {
        return view('livewire.teams');
    }
}
