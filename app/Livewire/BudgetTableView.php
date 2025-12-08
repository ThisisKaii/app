<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Budgets;
use App\Models\BudgetCategory;
use App\Models\Expenses;
use Illuminate\Support\Facades\Gate;

class BudgetTableView extends Component
{
    public $boardId;
    public $board;
    public $budget;

    // Filter properties
    public $statusFilter = '';
    public $searchFilter = '';
    public $showFilters = false;

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

    protected $listeners = [
        'category-updated' => '$refresh',
        'budget-updated' => 'loadBudget',
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

    public function loadBudget()
    {
        $this->budget = Budgets::firstOrCreate(
            ['board_id' => $this->boardId],
            ['total_budget' => 0]
        );
    }

    public function getCategories()
    {
        $query = BudgetCategory::where('budget_id', $this->budget->id)
            ->with(['expenses'])
            ->orderBy('order');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->searchFilter) {
            $query->where('title', 'like', '%' . $this->searchFilter . '%');
        }

        return $query->get();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->statusFilter = '';
        $this->searchFilter = '';
    }

    // Category Management
    public function openCategoryModal($categoryId = null)
    {
        // Fix: Use board permissions instead of task permissions
        if (!Gate::allows('update', $this->board)) {
            session()->flash('error', 'You are not authorized to manage categories.');
            return;
        }

        $this->resetCategoryForm();

        if ($categoryId) {
            $category = BudgetCategory::find($categoryId);
            if (!$category)
                return;

            $this->categoryId = $categoryId;
            $this->categoryTitle = $category->title;
            $this->categoryDescription = $category->description ?? '';
            $this->amountEstimated = number_format($category->amount_estimated, 2, '.', '');
            $this->categoryStatus = $category->status;
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
            } else {
                $maxOrder = BudgetCategory::where('budget_id', $this->budget->id)->max('order') ?? -1;
                $data['budget_id'] = $this->budget->id;
                $data['order'] = $maxOrder + 1;
                BudgetCategory::create($data);
            }

            session()->flash('success', 'Category saved successfully!');
            $this->closeCategoryModal();
            $this->dispatch('budget-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save category: ' . $e->getMessage());
        }
    }

    public function confirmDeleteCategory($categoryId)
    {
        $this->deleteCategoryId = $categoryId;
        $this->showDeleteCategoryModal = true;
        if ($this->showCategoryModal) {
            $this->closeCategoryModal();
        }
    }

    public function deleteCategory()
    {
        // Fix: Use board permissions instead of task permissions
        if (!Gate::allows('delete', $this->board)) {
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

    // Expense Management
    public function openExpenseModal($categoryId, $expenseId = null)
    {
        // Fix: Use board permissions instead of task permissions
        if (!Gate::allows('update', $this->board)) {
            session()->flash('error', 'You are not authorized to manage expenses.');
            return;
        }

        $this->resetExpenseForm();
        $this->selectedCategoryId = $categoryId;

        if ($expenseId) {
            $expense = Expenses::find($expenseId);
            if (!$expense)
                return;

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
            } else {
                $data['budget_category_id'] = $this->selectedCategoryId;
                Expenses::create($data);
            }

            session()->flash('success', 'Expense saved successfully!');
            $this->closeExpenseModal();
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
        // Fix: Use board permissions instead of task permissions
        if (!Gate::allows('delete', $this->board)) {
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
        $categories = $this->getCategories();
        $totalAllocated = $this->budget->getTotalAllocated();
        $totalSpent = $this->budget->getTotalSpent();
        $remaining = $this->budget->getRemainingBudget();

        return view('livewire.budget-table-view', [
            'categories' => $categories,
            'totalAllocated' => $totalAllocated,
            'totalSpent' => $totalSpent,
            'remaining' => $remaining,
        ]);
    }
}