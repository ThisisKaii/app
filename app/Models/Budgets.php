<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budgets extends Model
{
    protected $fillable = [
        'board_id',
        'total_budget',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function budgetTasks()
    {
        return $this->hasMany(BudgetCategory::class);
    }
}
