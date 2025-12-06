<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = ['user_id', 'title', 'list_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'board_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function budgets()
    {
        return $this->hasOne(Budgets::class);
    }


}
