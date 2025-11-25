<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;

class dashboardController extends Controller
{
    public function showBoard(Request $request)
    {
        
        $boards = Board::where('user_id', $request->user()->id)->get();

        return view('dashboard', ['boards' => $boards]);
    }
}
