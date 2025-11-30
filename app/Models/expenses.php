<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    protected $fillable = [
        'budget_task_id',
        'amount',
        'description',
    ];

    // Relationship: Expense belongs to a BudgetTask
    public function budgetTask()
    {
        return $this->belongsTo(BudgetCategory::class);
    }
}
