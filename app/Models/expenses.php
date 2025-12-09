<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use Loggable;

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

    // ============================================
    // Activity Logging Customization
    // ============================================

    protected function getLoggableFields()
    {
        return ['amount', 'description'];
    }

    protected function getLogBoardId()
    {
        // Expenses -> BudgetCategory -> Budget -> Board
        return $this->budgetCategory?->budget?->board_id;
    }

    protected function customCreatedDescription()
    {
        $desc = $this->description ? " ({$this->description})" : "";
        $category = $this->budgetCategory ? " to '{$this->budgetCategory->title}'" : "";
        return "Added expense of $" . number_format($this->amount, 2) . $desc . $category;
    }

    protected function customUpdatedDescription($changes)
    {
        $parts = [];
        
        if (isset($changes['amount'])) {
            $old = '$' . number_format($changes['amount']['old'], 2);
            $new = '$' . number_format($changes['amount']['new'], 2);
            $parts[] = "amount changed from {$old} to {$new}";
        }
        
        if (isset($changes['description'])) {
            $parts[] = "description updated to '{$changes['description']['new']}'";
        }
        
        $category = $this->budgetCategory ? " in '{$this->budgetCategory->title}'" : "";
        return "Updated expense" . $category . ": " . implode(', ', $parts);
    }

    protected function customDeletedDescription()
    {
        $desc = $this->description ? " ({$this->description})" : "";
        $category = $this->budgetCategory ? " from '{$this->budgetCategory->title}'" : "";
        return "Deleted expense of $" . number_format($this->amount, 2) . $desc . $category;
    }
}