<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetCategory extends Model
{
    protected $fillable = [
        'budget_id',
        'title',
        'amount_estimated',
        'status',
        'description',
    ];

    // Relationship: BudgetTask belongs to a Budget
    public function budget()
    {
        return $this->belongsTo(Budgets::class);
    }

    // Relationship: BudgetTask has many Expenses
    public function expenses()
    {
        return $this->hasMany(Expenses::class);
    }
}
