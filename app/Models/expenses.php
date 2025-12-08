<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    protected $fillable = [
        'budget_category_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationship to BudgetCategory
    public function budgetCategory()
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    // Alternative name for the relationship
    public function category()
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }
}