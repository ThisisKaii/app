<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Budgets;
use App\Models\BudgetCategory;
use App\Models\Expenses;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class BudgetBoard extends Component
{
    public $board;
    public $boardId;
    public $budget;
    public $categories = [];

    // Budget Modal
    public $showBudgetModal = false;
    public $totalBudget = 0;
    public $formattedTotalBudget = '';

    // Category Management
    public $showCategoryModal = false;
    public $categoryId = null;
    public $categoryTitle = '';
    public $categoryDescription = '';
    public $amountEstimated = '';
    public $categoryStatus = 'draft';

    // Expense Management
    public $showExpenseModal = false;
    public $selectedCategoryId = null;
    public $expenseId = null;
    public $expenseAmount = '';
    public $expenseDescription = '';

    // Delete Modals
    public $showDeleteCategoryModal = false;
    public $deleteCategoryId = null;
    public $showDeleteExpenseModal = false;
    public $deleteExpenseId = null;

    protected $listeners = [
        'budget-updated' => 'loadBudget',
        'category-updated' => 'loadCategories',
        'refresh-budget' => 'loadBudget',
        'refresh-categories' => 'loadCategories',
        'open-category-modal' => 'openCategoryModal', // General listener
    ];

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->board = Board::findOrFail($boardId);

        // Ensure this is a business board
        if ($this->board->list_type !== 'Business') {
            abort(403, 'This board is not a budget board');
        }

        $this->loadBudget();
    }

    public function loadBudget()
    {
        // Load or create budget
        $this->budget = Budgets::firstOrCreate(
            ['board_id' => $this->boardId],
            ['total_budget' => 0]
        );

        $this->loadCategories();
    }

    public function loadCategories()
    {
        // Load all categories for this budget, ordered by our implementation
        $this->categories = BudgetCategory::where('budget_id', $this->budget->id)
            ->with(['expenses'])
            ->orderBy('order')
            ->get();
    }

    // ============================================
    // Budget Management
    // ============================================

    public function openBudgetModal()
    {
        // Check authorization
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
        // Sanitize input: remove anything that isn't a digit or dot
        $sanitized = preg_replace('/[^0-9.]/', '', $this->totalBudget);
        $this->totalBudget = floatval($sanitized);

        $this->validate([
            'totalBudget' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
        ]);

        try {
            // Check if new budget covers existing allocations
            $totalAllocated = $this->budget->getTotalAllocated();
            $totalSpent = $this->budget->getTotalSpent();

            // Warn if budget is less than allocated (handled in UI), but allow save.
            // Only block if less than spent? Or allow that too?
            // User said "input lower than the allocated amount".
            // I will keep the check for SPENT as that is logically impossible to have budget < spent (you're in debt).
            // But allocated is just a plan.

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
    // updatedTotalBudget removed to prevent live hook interference

    // ============================================
    // Category Management
    // ============================================

    public function openCategoryModal($categoryId = null, $status = 'draft')
    {
        // Handle array payload
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

        // Check authorization
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
            $this->loadBudget(); // Reload everything
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save category: ' . $e->getMessage());
        }
    }

    public function confirmDeleteCategory($categoryId)
    {
        $this->deleteCategoryId = $categoryId;
        $this->showDeleteCategoryModal = true;
        // Close the edit modal if open
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

    // ============================================
    // Expense Management
    // ============================================

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