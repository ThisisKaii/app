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

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function budgetTasks()
    {
        return $this->hasMany(BudgetCategory::class);
    }

    // ============================================
    // Activity Logging Customization
    // ============================================

    protected function getLoggableAttributes()
    {
        return ['total_budget'];
    }

    protected function getAttributeLabel($attribute)
    {
        return $attribute === 'total_budget' ? 'Total budget' : parent::getAttributeLabel($attribute);
    }

    protected function formatValue($value)
    {
        // Format budget as currency
        if (debug_backtrace()[1]['args'][0] ?? null === 'total_budget') {
            return '$' . number_format($value, 2);
        }
        return parent::formatValue($value);
    }

    protected function getCreatedDescription()
    {
        return "Set initial budget: " . '$' . number_format($this->total_budget, 2);
    }

    protected function getUpdatedDescription($changes)
    {
        if (isset($changes['total_budget'])) {
            $old = '$' . number_format($changes['total_budget']['old'], 2);
            $new = '$' . number_format($changes['total_budget']['new'], 2);
            return "Updated total budget from {$old} to {$new}";
        }
        return parent::getUpdatedDescription($changes);
    }

    protected function getDeletedDescription()
    {
        return "Deleted budget (\$" . number_format($this->total_budget, 2) . ")";
    }
}