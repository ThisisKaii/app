<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class Individual extends Component
{
    public $myTasks;

    public function mount()
    {
        $this->myTasks = Task::where('assignee_id', Auth::id())->orderBy('created_at', 'desc')->get();
    }
    public function render()
    {
        return view('livewire.individual');
    }
}
