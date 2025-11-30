<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TasksList extends Component
{
    public $view = 'individual'; // individual | teams
    public function switchView($view)
    {
        $this->view = $view;
    }


    public function render()
    {
        return view('livewire.tasks-list', [
            'myTasks' => Task::where('assignee_id', Auth::id())->orderBy('created_at', 'desc')->get(),
            'teamMembers' => User::with('tasks')->get(),
            'unassignedTasks' => Task::whereNull('assignee_id')->get()
        ]);
    }
}
