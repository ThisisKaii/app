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
        'amount_estimated',
        'status',
        'description',
    ];

    public function budget()
    {
        return $this->belongsTo(Budgets::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expenses::class, 'budget_task_id');
    }

    // ============================================
    // Activity Logging Customization
    // ============================================

    protected function getBoardId()
    {
        return $this->budget?->board_id;
    }

    protected function getLoggableAttributes()
    {
        return ['title', 'amount_estimated', 'status', 'description'];
    }

    protected function getAttributeLabel($attribute)
    {
        $labels = [
            'amount_estimated' => 'Estimated amount',
        ];
        return $labels[$attribute] ?? parent::getAttributeLabel($attribute);
    }

    protected function formatValue($value)
    {
        // Format amounts as currency
        if (debug_backtrace()[1]['args'][0] ?? null === 'amount_estimated') {
            return '$' . number_format($value, 2);
        }
        return parent::formatValue($value);
    }

    protected function getCreatedDescription()
    {
        return "Created budget category '{$this->title}' (\$" . number_format($this->amount_estimated, 2) . ")";
    }

    protected function getUpdatedDescription($changes)
    {
        // Special handling for amount changes
        if (isset($changes['amount_estimated']) && count($changes) === 1) {
            $old = '$' . number_format($changes['amount_estimated']['old'], 2);
            $new = '$' . number_format($changes['amount_estimated']['new'], 2);
            return "Updated '{$this->title}' budget from {$old} to {$new}";
        }

        // Special handling for status changes
        if (isset($changes['status']) && count($changes) === 1) {
            return "Changed '{$this->title}' status from {$changes['status']['old']} to {$changes['status']['new']}";
        }

        return parent::getUpdatedDescription($changes);
    }

    protected function getDeletedDescription()
    {
        return "Deleted budget category '{$this->title}'";
    }

    // Helper: Get total spent
    public function getTotalSpent()
    {
        return $this->expenses()->sum('amount');
    }

    // Helper: Get remaining budget
    public function getRemainingBudget()
    {
        return $this->amount_estimated - $this->getTotalSpent();
    }
}