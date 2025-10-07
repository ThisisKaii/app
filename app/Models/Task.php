<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'board_id', 
        'user_id', 
        'title', 
        'status', 
        'type', 
        'priority',
        'assignee_id', 
        'due_date', 
        'url', 
        'description', 
        'order', 
        'completed_at'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }

}