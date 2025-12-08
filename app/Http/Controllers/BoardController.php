<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use App\Models\Budgets;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BoardController extends Controller
{
    public function show(Board $board)
    {
        // DEBUG: Log what we're loading
        \Log::info('Loading board in show method', [
            'board_id' => $board->id,
            'list_type' => $board->list_type,
            'title' => $board->title,
        ]);

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

        // For business boards, ensure budget exists
        if ($board->list_type === 'Business') {
            \Log::info('This is a BUSINESS board, creating/finding budget');
            $budget = Budgets::firstOrCreate(
                ['board_id' => $board->id],
                ['total_budget' => 0]
            );
        } else {
            \Log::info('This is a NORMAL board, loading tasks');
        }

        // Get tasks (for normal boards) or empty collection (for business boards)
        if ($board->list_type === 'Normal') {
            $tasks = $board->tasks()
                ->with(['assignee', 'tags'])
                ->orderBy('order')
                ->get();
        } else {
            $tasks = collect(); // Empty collection for business boards
        }

        return view('todobido', [
            'board' => $board,
            'task' => $tasks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'list_type' => 'required|in:normal,business',
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

        // Create initial budget for business boards
        if ($board->list_type === 'Business') {
            Budgets::create([
                'board_id' => $board->id,
                'total_budget' => 0,
            ]);
        }

        $boardType = $board->list_type === 'Business' ? 'Business' : 'Normal';

        return redirect()->route('boards.show', $board)
            ->with('success', "{$boardType} board created successfully!");
    }

    public function update(Request $request, Board $board)
    {
        Gate::authorize('update', $board);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $oldTitle = $board->title;
        $board->update($validated);

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