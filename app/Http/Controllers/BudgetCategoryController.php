<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Budgets;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * Handles API requests for budget category drag-and-drop status updates.
 */
class BudgetCategoryController extends Controller
{
    /**
     * Update a budget category's status and/or order position.
     * Used by the Kanban drag-and-drop functionality.
     *
     * @param Request $request
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $categoryId)
    {
        $category = BudgetCategory::find($categoryId);

        if (!$category) {
            Log::error('Budget category not found', ['category_id' => $categoryId]);
            return response()->json([
                'success' => false,
                'message' => 'Budget category not found'
            ], 404);
        }

        $budget = $category->budget;
        if (!$budget) {
            Log::error('Budget not found for category', ['category_id' => $categoryId]);
            return response()->json([
                'success' => false,
                'message' => 'Budget not found'
            ], 404);
        }

        try {
            Gate::authorize('updateTask', $budget->board);
        } catch (\Exception $e) {
            Log::error('Authorization failed for budget category update', [
                'category_id' => $categoryId,
                'user_id' => auth()->id(),
                'board_id' => $budget->board_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: ' . $e->getMessage()
            ], 403);
        }

        try {
            $validated = $request->validate([
                'status' => 'required|in:draft,pending,approved,rejected,completed',
                'new_order' => 'nullable|integer|min:0'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 422);
        }

        try {
            $category->update([
                'status' => $validated['status'],
                'order' => $validated['new_order'] ?? $category->order,
            ]);

            ActivityLog::log(
                $category->budget->board_id,
                BudgetCategory::class,
                $category->id,
                'update_status',
                "Changed status to " . ucfirst($validated['status'])
            );

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