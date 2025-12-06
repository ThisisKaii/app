<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Board;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'board_id' => 'required|exists:boards,id',
            'title' => 'required|string|max:255',
            'status' => 'required|in:to_do,in_review,in_progress,published',
            'type' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'url' => 'nullable|url|max:500',
            'description' => 'nullable|string',
        ]);

        // Authorization check
        $board = Board::findOrFail($validated['board_id']);
        Gate::authorize('createTask', $board);

        $validated['user_id'] = auth()->id();

        // Get the max order for this status
        $maxOrder = Task::where('board_id', $validated['board_id'])
            ->where('status', $validated['status'])
            ->where('user_id', auth()->id())
            ->max('order') ?? -1;

        $validated['order'] = $maxOrder + 1;

        $task = Task::create($validated);

        // Log activity
        ActivityLog::log(
            $board->id,
            'Task',
            $task->id,
            'created',
            auth()->user()->name . ' created task "' . $task->title . '"'
        );

        return redirect()->back()->with('success', 'Task created successfully!');
    }

    public function updateStatus(Request $request, Task $task)
    {
        // Authorization check
        $board = $task->board;
        Gate::authorize('viewTasks', $board);

        $validated = $request->validate([
            'status' => 'required|in:to_do,in_review,in_progress,published',
            'new_order' => 'nullable|integer|min:0'
        ]);

        try {
            $oldStatus = $task->status;
            
            $task->status = $validated['status'];
            $task->order = $validated['new_order'] ?? 0;
            $task->save();

            // Log activity only if status changed
            if ($oldStatus !== $validated['status']) {
                ActivityLog::log(
                    $board->id,
                    'Task',
                    $task->id,
                    'status_changed',
                    auth()->user()->name . ' moved "' . $task->title . '" from ' . 
                    str_replace('_', ' ', $oldStatus) . ' to ' . str_replace('_', ' ', $validated['status'])
                );
            }

            \Log::info('Task updated successfully', [
                'task_id' => $task->id,
                'status' => $task->status,
                'order' => $task->order
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Update failed', [
                'error' => $e->getMessage(),
                'task_id' => $task->id
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        // Authorization check
        $board = $task->board;
        Gate::authorize('viewTasks', $board);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:to_do,in_review,in_progress,published',
            'type' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'url' => 'nullable|url|max:500',
            'description' => 'nullable|string',
        ]);

        $changes = [];
        foreach ($validated as $key => $value) {
            if ($task->$key != $value) {
                $changes[] = $key;
            }
        }

        $task->update($validated);

        // Log activity
        if (!empty($changes)) {
            ActivityLog::log(
                $board->id,
                'Task',
                $task->id,
                'updated',
                auth()->user()->name . ' updated task "' . $task->title . '" (' . implode(', ', $changes) . ')'
            );
        }

        return redirect()->back()->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        // Authorization check
        $board = $task->board;
        Gate::authorize('viewTasks', $board);

        $title = $task->title;
        
        // Log activity before deletion
        ActivityLog::log(
            $board->id,
            'Task',
            null,
            'deleted',
            auth()->user()->name . ' deleted task "' . $title . '"'
        );

        $task->delete();

        return redirect()->back()->with('success', 'Task deleted successfully!');
    }
}