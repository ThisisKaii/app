<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class Individual extends Component
{
    public $myTasks;
    public $boardId;

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->myTasks = Task::where('assignee_id', Auth::id())
            ->where('board_id', $this->boardId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function render()
    {
        return view('livewire.individual');
    }
}
