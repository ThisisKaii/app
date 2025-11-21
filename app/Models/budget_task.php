<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class budget_task extends Model
{
    protected $fillable = ['budget_id', 'title', 'status'];

    public function budgets(){
        return $this->belongsTo(Budgets::class);
    }

    public function expenses()
    {
        return $this->hasMany(expenses::class);
    }
}
