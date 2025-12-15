<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Budgets;
use App\Models\BudgetCategory;
use App\Models\Expenses;
use App\Models\Board;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;

class BudgetCategoryModal extends Component
{
    public $budget;
    public $status;
    public $categories = [];
    public $board;

    // Category Modal
    public $showCategoryModal = false;
    public $categoryId = null;
    public $categoryTitle = '';
    public $categoryDescription = '';
    public $amountEstimated = '';
    public $categoryStatus = 'draft';

    // Expense Modal
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

    public function getListeners()
    {
        return [
            "open-category-modal-{$this->status}" => 'openCategoryModal',
            'category-updated' => 'loadCategories',
            'refresh-categories' => 'loadCategories',
        ];
    }

    public function mount(Budgets $budget, $status, Board $board)
    {
        $this->budget = $budget;
        $this->status = $status;
        $this->board = $board;
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = BudgetCategory::where('budget_id', $this->budget->id)
            ->where('status', $this->status)
            ->with(['expenses'])
            ->orderBy('order')
            ->get();
    }

    // ============================================
    // Category Management
    // ============================================

    public function openCategoryModal($categoryId = null)
    {
        // Handle array payload (common in Livewire event dispatch)
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
            $this->categoryStatus = $this->status;
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

                ActivityLog::log(
                    $this->board->id,
                    BudgetCategory::class,
                    $category->id,
                    'update',
                    "Updated category details"
                );
            } else {
                $maxOrder = BudgetCategory::where('budget_id', $this->budget->id)
                    ->where('status', $this->categoryStatus)
                    ->max('order') ?? -1;

                $data['budget_id'] = $this->budget->id;
                $data['order'] = $maxOrder + 1;

                $category = BudgetCategory::create($data);

                ActivityLog::log(
                    $this->board->id,
                    BudgetCategory::class,
                    $category->id,
                    'create',
                    "Created category '{$category->title}'"
                );
            }

            session()->flash('success', 'Category saved successfully!');
            $this->closeCategoryModal();
            $this->dispatch('category-updated');
            $this->dispatch('budget-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save category: ' . $e->getMessage());
        }
    }

    public function confirmDeleteCategory($categoryId)
    {
        $this->deleteCategoryId = $categoryId;
        $this->showDeleteCategoryModal = true;
        $this->closeCategoryModal();
    }

    public function deleteCategory()
    {
        // Check authorization
        if (!Gate::allows('deleteTask', $this->board)) {
            session()->flash('error', 'You are not authorized to delete categories.');
            return;
        }

        try {
            $category = BudgetCategory::find($this->deleteCategoryId);
            if ($category) {
                ActivityLog::log(
                    $this->board->id,
                    BudgetCategory::class,
                    $category->id,
                    'delete',
                    "Deleted category '{$category->title}'"
                );
                $category->delete();
                session()->flash('success', 'Category deleted successfully!');
            }

            $this->closeDeleteCategoryModal();
            $this->dispatch('category-updated');
            $this->dispatch('budget-updated');
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
        $this->loadCategories();
    }

    protected function resetCategoryForm()
    {
        $this->categoryId = null;
        $this->categoryTitle = '';
        $this->categoryDescription = '';
        $this->amountEstimated = '';
        $this->categoryStatus = $this->status;
        $this->resetValidation();
    }

    // ============================================
    // Expense Management
    // ============================================

    public function openExpenseModal($categoryId, $expenseId = null)
    {
        // Check authorization
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

        try {
            $data = [
                'amount' => $this->expenseAmount,
                'description' => $this->expenseDescription,
            ];

            if ($this->expenseId) {
                $expense = Expenses::find($this->expenseId);
                $expense->update($data);

                ActivityLog::log(
                    $this->board->id,
                    BudgetCategory::class,
                    $this->selectedCategoryId,
                    'update_expense',
                    "Updated expense amount to $" . number_format($this->expenseAmount, 2)
                );
            } else {
                $data['budget_category_id'] = $this->selectedCategoryId;
                Expenses::create($data);

                ActivityLog::log(
                    $this->board->id,
                    BudgetCategory::class,
                    $this->selectedCategoryId,
                    'add_expense',
                    "Added expense of $" . number_format($this->expenseAmount, 2)
                );
            }

            session()->flash('success', 'Expense saved successfully!');
            $this->closeExpenseModal();
            $this->dispatch('category-updated');
            $this->dispatch('budget-updated');
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
        // Check authorization
        if (!Gate::allows('addExpense', $this->board)) {
            session()->flash('error', 'You are not authorized to delete expenses.');
            return;
        }

        try {
            $expense = Expenses::find($this->deleteExpenseId);
            if ($expense) {
                ActivityLog::log(
                    $this->board->id,
                    BudgetCategory::class,
                    $expense->budget_category_id,
                    'delete_expense',
                    "Deleted expense of $" . number_format($expense->amount, 2)
                );
                $expense->delete();
                session()->flash('success', 'Expense deleted successfully!');
            }

            $this->closeDeleteExpenseModal();
            $this->dispatch('category-updated');
            $this->dispatch('budget-updated');
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
        return view('livewire.budget-category-modal');
    }
}