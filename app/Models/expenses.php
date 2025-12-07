<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use Loggable;

    protected $fillable = [
        'budget_task_id',
        'amount',
        'description',
    ];

    public function budgetTask()
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_task_id');
    }

    // ============================================
    // Activity Logging Customization
    // ============================================

    protected function getBoardId()
    {
        return $this->budgetTask?->budget?->board_id;
    }

    protected function getLoggableAttributes()
    {
        return ['amount', 'description'];
    }

    protected function formatValue($value)
    {
        // Format amounts as currency
        if (debug_backtrace()[1]['args'][0] ?? null === 'amount') {
            return '$' . number_format($value, 2);
        }
        return parent::formatValue($value);
    }

    protected function getCreatedDescription()
    {
        $categoryName = $this->budgetTask?->title ?? 'Unknown category';
        $amount = '$' . number_format($this->amount, 2);
        $desc = $this->description ? " ({$this->description})" : '';
        return "Added expense of {$amount} to '{$categoryName}'{$desc}";
    }

    protected function getUpdatedDescription($changes)
    {
        $categoryName = $this->budgetTask?->title ?? 'Unknown category';

        // Special handling for amount changes
        if (isset($changes['amount']) && count($changes) === 1) {
            $old = '$' . number_format($changes['amount']['old'], 2);
            $new = '$' . number_format($changes['amount']['new'], 2);
            return "Updated expense amount from {$old} to {$new} in '{$categoryName}'";
        }

        return "Updated expense in '{$categoryName}'";
    }

    protected function getDeletedDescription()
    {
        $categoryName = $this->budgetTask?->title ?? 'Unknown category';
        $amount = '$' . number_format($this->amount, 2);
        return "Deleted expense of {$amount} from '{$categoryName}'";
    }
}
