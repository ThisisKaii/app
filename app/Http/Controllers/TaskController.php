<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

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

        $board = Board::findOrFail($validated['board_id']);
        Gate::authorize('createTask', $board);

        $validated['user_id'] = auth()->id();
        $validated['order'] = Task::where('board_id', $validated['board_id'])
            ->where('status', $validated['status'])
            ->max('order') + 1 ?? 0;

        Task::create($validated);

        return redirect()->back()->with('success', 'Task created!');
    }

    public function updateStatus(Request $request, $taskId)
    {
        // Log the incoming request for debugging
        Log::info('Task update request', [
            'task_id' => $taskId,
            'request_data' => $request->all()
        ]);

        // Find the task
        $task = Task::find($taskId);

        if (!$task) {
            Log::error('Task not found', ['task_id' => $taskId]);
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        // Check authorization
        try {
            Gate::authorize('viewTasks', $task->board);
        } catch (\Exception $e) {
            Log::error('Authorization failed', [
                'task_id' => $taskId,
                'user_id' => auth()->id(),
                'board_id' => $task->board_id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: ' . $e->getMessage()
            ], 403);
        }

        // Validate the request
        try {
            $validated = $request->validate([
                'status' => 'required|in:to_do,in_review,in_progress,published',
                'new_order' => 'nullable|integer|min:0'
            ]);
        } catch (\Exception $e) {
            Log::error('Validation failed', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 422);
        }

        // Update the task
        try {
            $task->update([
                'status' => $validated['status'],
                'order' => $validated['new_order'] ?? $task->order,
            ]);

            Log::info('Task updated successfully', [
                'task_id' => $taskId,
                'new_status' => $validated['status']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Task update failed', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        Gate::authorize('viewTasks', $task->board);

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

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated!');
    }

    public function destroy(Task $task)
    {
        Gate::authorize('viewTasks', $task->board);
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted!');
    }
}