<?php

namespace App\Http\Controllers;

use App\Models\Board;

class BoardController extends Controller
{
    public function show(Board $board)
    {

        if (auth()->check()) {
            $tasks = $board->tasks()
                ->where('user_id', auth()->id())
                ->with(['assignee', 'tags'])
                ->orderBy('order')
                ->get();

            $taskLimit = null;
            $remainingTasks = null;
        } else {
            // Guest users
            $guestSessionId = session('guest_session_id');

            $tasks = $board->tasks()
                ->where('session_id', $guestSessionId)
                ->with(['assignee', 'tags'])
                ->orderBy('order')
                ->get();

            $taskLimit = 5;
        }

        return view('todo', compact('board', 'tasks', 'taskLimit'));
    }
}
