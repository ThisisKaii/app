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

        Task::create($validated);

        return redirect()->back()->with('success', 'Task created successfully!');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:to_do,in_review,in_progress,published'
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json(['success' => true]);
    }
}
