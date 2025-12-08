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
    public $totalBudget = '';

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
        // Check authorization - use 'update' policy instead of 'updateTask'
        if (!Gate::allows('update', $this->board)) {
            session()->flash('error', 'You are not authorized to update the budget.');
            return;
        }

        // Format the number without thousands separator
        $this->totalBudget = number_format($this->budget->total_budget, 2, '.', '');
        $this->showBudgetModal = true;
        
        // Force Livewire to re-render
        $this->dispatch('budget-modal-opened');
    }

    public function saveBudget()
    {
        $this->validate([
            'totalBudget' => 'required|numeric|min:0|max:999999999.99',
        ]);

        try {
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
        $this->totalBudget = '';
        $this->resetValidation();
    }

    public function render()
    {
        // Recalculate on each render to ensure fresh data
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