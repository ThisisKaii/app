<?php

namespace App\Http\Controllers;

use App\Models\Board;

class BoardController extends Controller
{
    public function show(Board $board)
    {
            #I REMOVE AFTER LIVEWIRE CONVERTION
            $tasks = $board->tasks()
                ->where('user_id', auth()->id())
                ->with(['assignee', 'tags'])
                ->orderBy('order')
                ->get();

            $taskLimit = null;
            $remainingTasks = null;
            $board->touch();
            
        return view('todobido', compact('board', 'tasks', 'taskLimit'));
    }
}
