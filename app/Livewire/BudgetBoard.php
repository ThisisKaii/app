<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Budgets;
use App\Models\BudgetCategory;
use Illuminate\Support\Facades\Gate;

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

    protected $listeners = [
        'budget-updated' => 'loadBudget',
        'category-updated' => 'loadCategories',
        'refresh-budget' => 'loadBudget',
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
        $this->categories = BudgetCategory::where('budget_id', $this->budget->id)
            ->with(['expenses'])
            ->orderBy('order')
            ->get();
    }

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
        // Convert formatted string back to float
        $this->totalBudget = floatval(str_replace(',', '', $this->totalBudget));

        $this->validate([
            'totalBudget' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
        ]);

        try {
            // Check if new budget covers existing allocations
            $totalAllocated = $this->budget->getTotalAllocated();
            $totalSpent = $this->budget->getTotalSpent();

            if ($this->totalBudget < $totalAllocated) {
                session()->flash('error', 'Budget cannot be less than allocated amount ($' . number_format($totalAllocated, 2) . ')');
                return;
            }

            if ($this->totalBudget < $totalSpent) {
                session()->flash('error', 'Budget cannot be less than spent amount ($' . number_format($totalSpent, 2) . ')');
                return;
            }

            $this->budget->update([
                'total_budget' => $this->totalBudget
            ]);

            session()->flash('success', 'Budget updated successfully!');
            $this->closeBudgetModal();
            $this->loadBudget();

            // Dispatch event to refresh other components
            $this->dispatch('budget-updated');

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

    public function updatedTotalBudget($value)
    {
        // Format the input as user types
        if (is_numeric(str_replace(',', '', $value))) {
            $this->totalBudget = floatval(str_replace(',', '', $value));
        }
    }

    public function render()
    {
        $this->loadCategories();

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