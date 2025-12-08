<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class Budgets extends Model
{
    use Loggable;

    protected $fillable = [
        'board_id',
        'total_budget',
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
    ];

    // Relationships
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function budgetCategories()
    {
        return $this->hasMany(BudgetCategory::class, 'budget_id')->orderBy('order');
    }

    // ============================================
    // Activity Logging Customization
    // ============================================

    protected function getLoggableFields()
    {
        return ['total_budget'];
    }

    protected function customCreatedDescription()
    {
        return "Set initial budget: $" . number_format($this->total_budget, 2);
    }

    protected function customUpdatedDescription($changes)
    {
        if (isset($changes['total_budget'])) {
            $old = '$' . number_format($changes['total_budget']['old'], 2);
            $new = '$' . number_format($changes['total_budget']['new'], 2);
            return "Updated total budget from {$old} to {$new}";
        }
        return $this->buildUpdateDescription($changes);
    }

    protected function customDeletedDescription()
    {
        return "Deleted budget (\$" . number_format($this->total_budget, 2) . ")";
    }

    // Helper methods
    public function getTotalAllocated()
    {
        return $this->budgetCategories()->sum('amount_estimated');
    }

    public function getTotalSpent()
    {
        return $this->budgetCategories->sum(function ($category) {
            return $category->getTotalSpent();
        });
    }

    public function getRemainingBudget()
    {
        return $this->total_budget - $this->getTotalSpent();
    }
}