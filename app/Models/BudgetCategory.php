<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetCategory extends Model
{
    protected $fillable = [
        'budget_id',
        'title',
        'description',
        'amount_estimated',
        'status',
        'order',
    ];

    protected $casts = [
        'amount_estimated' => 'decimal:2',
        'order' => 'integer',
    ];

    // Relationship to Budget
    public function budget()
    {
        return $this->belongsTo(Budgets::class, 'budget_id');
    }

    // Relationship to Expenses - FIXED: Should reference 'budget_category_id' not 'budget_task_id'
    public function expenses()
    {
        return $this->hasMany(Expenses::class, 'budget_category_id');
    }

    // Helper Methods
    public function getTotalSpent()
    {
        return $this->expenses()->sum('amount') ?? 0;
    }

    public function getRemainingBudget()
    {
        return $this->amount_estimated - $this->getTotalSpent();
    }

    public function getProgressPercentage()
    {
        if ($this->amount_estimated <= 0) {
            return 0;
        }
        return ($this->getTotalSpent() / $this->amount_estimated) * 100;
    }

    public function isOverBudget()
    {
        return $this->getTotalSpent() > $this->amount_estimated;
    }
}