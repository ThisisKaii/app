<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Budgets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class BudgetCategoryController extends Controller
{
    public function updateStatus(Request $request, $categoryId)
    {
        // Log the incoming request for debugging
        Log::info('Budget category update request', [
            'category_id' => $categoryId,
            'request_data' => $request->all()
        ]);

        // Find the budget category
        $category = BudgetCategory::find($categoryId);

        if (!$category) {
            Log::error('Budget category not found', ['category_id' => $categoryId]);
            return response()->json([
                'success' => false,
                'message' => 'Budget category not found'
            ], 404);
        }

        // Get the budget and check authorization
        $budget = $category->budget;
        if (!$budget) {
            Log::error('Budget not found for category', ['category_id' => $categoryId]);
            return response()->json([
                'success' => false,
                'message' => 'Budget not found'
            ], 404);
        }

        // Check authorization through the board
        try {
            Gate::authorize('viewTasks', $budget->board);
        } catch (\Exception $e) {
            Log::error('Authorization failed', [
                'category_id' => $categoryId,
                'user_id' => auth()->id(),
                'board_id' => $budget->board_id,
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
                'status' => 'required|in:draft,pending,approved,rejected,completed',
                'new_order' => 'nullable|integer|min:0'
            ]);
        } catch (\Exception $e) {
            Log::error('Validation failed', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 422);
        }

        // Update the category
        try {
            $category->update([
                'status' => $validated['status'],
                'order' => $validated['new_order'] ?? $category->order,
            ]);

            Log::info('Budget category updated successfully', [
                'category_id' => $categoryId,
                'new_status' => $validated['status'],
                'new_order' => $validated['new_order'] ?? $category->order
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Budget category updated successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Budget category update failed', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }
}