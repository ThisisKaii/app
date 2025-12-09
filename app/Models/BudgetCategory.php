<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class BudgetCategory extends Model
{
    use Loggable;
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

    // ============================================
    // Activity Logging Customization
    // ============================================

    protected function getLoggableFields()
    {
        return ['title', 'description', 'amount_estimated', 'status'];
    }

    protected function getLogBoardId()
    {
        // BudgetCategory -> Budget -> Board
        return $this->budget?->board_id;
    }

    protected function customCreatedDescription()
    {
        return "Created category '{$this->title}' with budget $" . number_format($this->amount_estimated, 2);
    }

    protected function customUpdatedDescription($changes)
    {
        $parts = [];
        
        if (isset($changes['title'])) {
            $parts[] = "renamed from '{$changes['title']['old']}' to '{$changes['title']['new']}'";
        }
        
        if (isset($changes['amount_estimated'])) {
            $old = '$' . number_format($changes['amount_estimated']['old'], 2);
            $new = '$' . number_format($changes['amount_estimated']['new'], 2);
            $parts[] = "budget changed from {$old} to {$new}";
        }
        
        if (isset($changes['status'])) {
            $parts[] = "status changed to '{$changes['status']['new']}'";
        }
        
        if (isset($changes['description'])) {
            $parts[] = "description updated";
        }
        
        return "Updated category '{$this->title}': " . implode(', ', $parts);
    }

    protected function customDeletedDescription()
    {
        return "Deleted category '{$this->title}' (Budget: $" . number_format($this->amount_estimated, 2) . ")";
    }
}