<?php

namespace App\Http\Controllers;
use App\Models\Board;

class BoardController extends Controller
{
    public function show(Board $board){
        $tasks = $board->tasks()
        ->with(['assignee', 'tags'])
        ->orderBy('order')
        ->get();

        return view('boards.show', compact('board', 'tasks'));
    }
}
