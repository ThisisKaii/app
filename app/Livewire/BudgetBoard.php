<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Budgets;
use App\Models\BudgetCategory;
use App\Models\Expenses;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Kanban-style budget board component for Business boards.
 * Manages budget categories, expenses, and provides drag-and-drop functionality.
 */
class BudgetBoard extends Component
{
    public $board;
    public $boardId;
    public $budget;
    public $categories = [];

    public $showBudgetModal = false;
    public $totalBudget = 0;
    public $formattedTotalBudget = '';

    public $showCategoryModal = false;
    public $categoryId = null;
    public $categoryTitle = '';
    public $categoryDescription = '';
    public $amountEstimated = '';
    public $categoryStatus = 'draft';

    public $showExpenseModal = false;
    public $selectedCategoryId = null;
    public $expenseId = null;
    public $expenseAmount = '';
    public $expenseDescription = '';

    public $showDeleteCategoryModal = false;
    public $deleteCategoryId = null;
    public $showDeleteExpenseModal = false;
    public $deleteExpenseId = null;

    protected $listeners = [
        'budget-updated' => 'loadBudget',
        'category-updated' => 'loadCategories',
        'refresh-budget' => 'loadBudget',
        'refresh-categories' => 'loadCategories',
        'open-category-modal' => 'openCategoryModal',
    ];

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->board = Board::findOrFail($boardId);

        if ($this->board->list_type !== 'Business') {
            abort(403, 'This board is not a budget board');
        }

        $this->loadBudget();
    }

    /**
     * Load or create the budget record for this board and refresh categories.
     */
    public function loadBudget()
    {
        $this->budget = Budgets::firstOrCreate(
            ['board_id' => $this->boardId],
            ['total_budget' => 0]
        );

        $this->loadCategories();
    }

    /**
     * Load all categories with their expenses, ordered by position.
     */
    public function loadCategories()
    {
        $this->categories = BudgetCategory::where('budget_id', $this->budget->id)
            ->with(['expenses'])
            ->orderBy('order')
            ->get();
    }

    /**
     * Open the total budget editing modal (Owner/Admin only).
     */

    public function openBudgetModal()
    {
        if (!Gate::allows('update', $this->board)) {
            session()->flash('error', 'You are not authorized to update the budget.');
            return;
        }

        $this->totalBudget = $this->budget->total_budget;
        $this->formattedTotalBudget = number_format($this->budget->total_budget, 2, '.', '');
        $this->showBudgetModal = true;
    }

    public function saveBudget()
    {
        if (!Gate::allows('update', $this->board)) {
            session()->flash('error', 'You are not authorized to update the budget.');
            return;
        }

        $sanitized = preg_replace('/[^0-9.]/', '', $this->totalBudget);
        $this->totalBudget = floatval($sanitized);

        $this->validate([
            'totalBudget' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
        ]);

        try {
            $totalSpent = $this->budget->getTotalSpent();

            if ($this->totalBudget < $totalSpent) {
                 $this->addError('totalBudget', 'Budget cannot be less than spent amount ($' . number_format($totalSpent, 2) . ')');
                return;
            }

            $this->budget->update([
                'total_budget' => $this->totalBudget
            ]);

            session()->flash('success', 'Budget updated successfully!');
            $this->closeBudgetModal();
            $this->loadBudget();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update budget: ' . $e->getMessage());
        }
    }

    public function closeBudgetModal()
    {
        $this->showBudgetModal = false;
        $this->totalBudget = 0;
        $this->formattedTotalBudget = '';
        $this->resetValidation();
    }

    /**
     * Open category modal for creating or editing a budget category.
     *
     * @param int|array|null $categoryId Category ID or array payload from event
     * @param string $status Initial status for new categories
     */
    public function openCategoryModal($categoryId = null, $status = 'draft')
    {
        if (is_array($categoryId)) {
            $categoryId = $categoryId['categoryId'] ?? null;
        }
        // Check authorization
        if (!Gate::allows('viewTasks', $this->board)) {
            session()->flash('error', 'You are not authorized to view categories.');
            return;
        }

        $this->resetCategoryForm();

        if ($categoryId) {
            $category = BudgetCategory::find($categoryId);
            if (!$category) return;

            $this->categoryId = $categoryId;
            $this->categoryTitle = $category->title;
            $this->categoryDescription = $category->description ?? '';
            $this->amountEstimated = number_format($category->amount_estimated, 2, '.', '');
            $this->categoryStatus = $category->status;
        } else {
            $this->categoryStatus = $status;
        }

        $this->showCategoryModal = true;
    }

    public function saveCategory()
    {
        $this->validate([
            'categoryTitle' => 'required|string|max:255',
            'amountEstimated' => 'required|numeric|min:0|max:999999999.99',
            'categoryStatus' => 'required|in:draft,pending,approved,rejected,completed',
        ]);

        $permission = $this->categoryId ? 'updateTask' : 'createTask';
        if (!Gate::allows($permission, $this->board)) {
            session()->flash('error', 'You are not authorized to save categories.');
            return;
        }

        try {
            $data = [
                'title' => $this->categoryTitle,
                'description' => $this->categoryDescription,
                'amount_estimated' => $this->amountEstimated,
                'status' => $this->categoryStatus,
            ];

            if ($this->categoryId) {
                $category = BudgetCategory::find($this->categoryId);
                $category->update($data);
            } else {
                $maxOrder = BudgetCategory::where('budget_id', $this->budget->id)
                    ->where('status', $this->categoryStatus)
                    ->max('order') ?? -1;

                $data['budget_id'] = $this->budget->id;
                $data['order'] = $maxOrder + 1;

                BudgetCategory::create($data);
            }

            session()->flash('success', 'Category saved successfully!');
            $this->closeCategoryModal();
            $this->loadBudget();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save category: ' . $e->getMessage());
        }
    }

    public function confirmDeleteCategory($categoryId)
    {
        $this->deleteCategoryId = $categoryId;
        $this->showDeleteCategoryModal = true;
        $this->showCategoryModal = false;
    }

    public function deleteCategory()
    {
        if (!Gate::allows('deleteTask', $this->board)) {
            session()->flash('error', 'You are not authorized to delete categories.');
            return;
        }

        try {
            $category = BudgetCategory::find($this->deleteCategoryId);
            if ($category) {
                $category->delete();
                session()->flash('success', 'Category deleted successfully!');
            }

            $this->closeDeleteCategoryModal();
            $this->loadBudget();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }

    public function closeDeleteCategoryModal()
    {
        $this->showDeleteCategoryModal = false;
        $this->deleteCategoryId = null;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    protected function resetCategoryForm()
    {
        $this->categoryId = null;
        $this->categoryTitle = '';
        $this->categoryDescription = '';
        $this->amountEstimated = '';
        $this->categoryStatus = 'draft';
        $this->resetValidation();
    }

    /**
     * Open expense modal for creating or editing an expense within a category.
     *
     * @param int $categoryId The parent category ID
     * @param int|null $expenseId Expense ID for editing, null for creating
     */
    public function openExpenseModal($categoryId, $expenseId = null)
    {
        if (!Gate::allows('addExpense', $this->board)) {
            session()->flash('error', 'You are not authorized to manage expenses.');
            return;
        }

        $this->resetExpenseForm();
        $this->selectedCategoryId = $categoryId;

        if ($expenseId) {
            $expense = Expenses::find($expenseId);
            if (!$expense) return;

            $this->expenseId = $expenseId;
            $this->expenseAmount = number_format($expense->amount, 2, '.', '');
            $this->expenseDescription = $expense->description ?? '';
        }

        $this->showExpenseModal = true;
    }

    public function saveExpense()
    {
        $this->validate([
            'expenseAmount' => 'required|numeric|min:0|max:999999999.99',
            'expenseDescription' => 'nullable|string|max:500',
        ]);

        if (!Gate::allows('addExpense', $this->board)) {
            session()->flash('error', 'You are not authorized to save expenses.');
            return;
        }

        try {
            $data = [
                'amount' => $this->expenseAmount,
                'description' => $this->expenseDescription,
            ];

            if ($this->expenseId) {
                $expense = Expenses::find($this->expenseId);
                $expense->update($data);
            } else {
                $data['budget_category_id'] = $this->selectedCategoryId;
                Expenses::create($data);
            }

            session()->flash('success', 'Expense saved successfully!');
            $this->closeExpenseModal();
            $this->loadBudget();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save expense: ' . $e->getMessage());
        }
    }

    public function confirmDeleteExpense($expenseId)
    {
        $this->deleteExpenseId = $expenseId;
        $this->showDeleteExpenseModal = true;
    }

    public function deleteExpense()
    {
        if (!Gate::allows('addExpense', $this->board)) {
            session()->flash('error', 'You are not authorized to delete expenses.');
            return;
        }

        try {
            $expense = Expenses::find($this->deleteExpenseId);
            if ($expense) {
                $expense->delete();
                session()->flash('success', 'Expense deleted successfully!');
            }

            $this->closeDeleteExpenseModal();
            $this->loadBudget();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete expense: ' . $e->getMessage());
        }
    }

    public function closeDeleteExpenseModal()
    {
        $this->showDeleteExpenseModal = false;
        $this->deleteExpenseId = null;
    }

    public function closeExpenseModal()
    {
        $this->showExpenseModal = false;
        $this->resetExpenseForm();
        // Since we are monolithic, we might want to keep the category modal open if we were coming from there, 
        // but typically expenses are added *from* the category modal.
        // For now, let's assume we return to the category modal if we were editing one?
        // Actually, the UI usually has expenses inside the category modal.
        // Let's ensure the categories are reloaded so the modal (if open) sees the new expense.
        $this->loadCategories(); 
    }

    protected function resetExpenseForm()
    {
        $this->expenseId = null;
        $this->selectedCategoryId = null;
        $this->expenseAmount = '';
        $this->expenseDescription = '';
        $this->resetValidation();
    }

    public function render()
    {
        $totalAllocated = $this->budget->getTotalAllocated();
        $totalSpent = $this->budget->getTotalSpent();
        $remaining = $this->budget->getRemainingBudget();

        return view('livewire.budget-board', [
            'totalAllocated' => $totalAllocated,
            'totalSpent' => $totalSpent,
            'remaining' => $remaining,
        ]);
    }
}