<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use App\Models\Budgets;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles CRUD operations for boards (both Normal and Business types).
 */
class BoardController extends Controller
{
    /**
     * Display the board with its tasks or budget data.
     * Automatically adds the creator as owner if not already a member.
     *
     * @param Board $board
     * @return \Illuminate\View\View
     */
    public function show(Board $board)
    {
        $isMember = $board->members()->where('user_id', auth()->id())->exists();

        if (!$isMember) {
            if ($board->user_id === auth()->id()) {
                $board->members()->attach(auth()->id(), [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $isMember = true;
            }
        }

        if (!$isMember) {
            abort(403, 'You do not have access to this board.');
        }

        if ($board->list_type === 'Business') {
            Budgets::firstOrCreate(
                ['board_id' => $board->id],
                ['total_budget' => 0]
            );
        }

        $tasks = $board->list_type === 'Normal'
            ? $board->tasks()->with(['assignee', 'tags'])->orderBy('order')->get()
            : collect();

        return view('todobido', [
            'board' => $board,
            'task' => $tasks,
        ]);
    }

    /**
     * Create a new board and set the authenticated user as owner.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

        $board->members()->attach(auth()->id(), [
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

    /**
     * Update the board title (Owner/Admin only).
     *
     * @param Request $request
     * @param Board $board
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Board $board)
    {
        Gate::authorize('update', $board);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $board->update($validated);

        return back()->with('success', 'Board updated successfully!');
    }

    /**
     * Permanently delete the board (Owner only).
     *
     * @param Board $board
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Board $board)
    {
        Gate::authorize('delete', $board);

        $title = $board->title;
        $board->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Board "' . $title . '" deleted successfully!');
    }
}