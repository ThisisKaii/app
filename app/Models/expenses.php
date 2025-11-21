<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class expenses extends Model
{
    protected $fillable = ['task_id', 'amount', 'date', 'description'];

    public function budgetTask()
    {
        return $this->belongsTo(budget_task::class);
    }
}
