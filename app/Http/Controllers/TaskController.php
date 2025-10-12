<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Tag;

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

        $validated['user_id'] = auth()->id();

        // Get the max order for this status
        $maxOrder = Task::where('board_id', $validated['board_id'])
            ->where('status', $validated['status'])
            ->where('user_id', auth()->id())
            ->max('order') ?? -1;

        $validated['order'] = $maxOrder + 1;

        Task::create($validated);
        return redirect()->back()->with('success', 'Task created successfully!');
    }

    public function updateStatus(Request $request, Task $task)
    {

        $validated = $request->validate([
            'status' => 'required|in:to_do,in_review,in_progress,published',
            'new_order' => 'nullable|integer|min:0'
        ]);

        try {
            // Simple update first - just status and order
            $task->status = $validated['status'];
            $task->order = $validated['new_order'] ?? 0;
            $task->save();

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
}
