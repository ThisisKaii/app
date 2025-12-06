<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BoardController extends Controller
{
    public function show(Board $board)
    {
        // Check if user is a board member, if not, try to add them as owner if they created it
        $isMember = $board->members()->where('user_id', auth()->id())->exists();
        
        if (!$isMember) {
            // If user is the creator but not a member, add them as owner
            if ($board->user_id === auth()->id()) {
                $board->members()->attach(auth()->id(), [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $isMember = true;
            }
        }
        
        // Authorization check
        if (!$isMember) {
            abort(403, 'You do not have access to this board.');
        }

        // Get tasks with proper relationships
        $tasks = $board->tasks()
            ->with(['assignee', 'tags'])
            ->where('user_id', auth()->id())
            ->orderBy('order')
            ->get();

        return view('todobido', [
            'board' => $board,
            'task' => $tasks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'list_type' => 'required|in:todo,budget',
        ]);

        $board = Board::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'list_type' => $validated['list_type'],
        ]);

        // Add creator as owner
        $board->members()->attach(auth()->id(), [
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log activity
        ActivityLog::log(
            $board->id,
            'Board',
            $board->id,
            'created',
            auth()->user()->name . ' created the board "' . $board->title . '"'
        );

        return redirect()->route('boards.show', $board)
            ->with('success', 'Board created successfully!');
    }

    public function update(Request $request, Board $board)
    {
        Gate::authorize('update', $board);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $oldTitle = $board->title;
        $board->update($validated);

        // Log activity
        ActivityLog::log(
            $board->id,
            'Board',
            $board->id,
            'updated',
            auth()->user()->name . ' renamed board from "' . $oldTitle . '" to "' . $board->title . '"'
        );

        return back()->with('success', 'Board updated successfully!');
    }

    public function destroy(Board $board)
    {
        Gate::authorize('delete', $board);

        $title = $board->title;
        $board->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Board "' . $title . '" deleted successfully!');
    }
}