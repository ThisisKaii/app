<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use Loggable;

    protected $fillable = ['name', 'color'];

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_tag');
    }

    // ============================================
    // Activity Logging Customization
    // ============================================

    /**
     * Tags don't belong to a specific board, so disable automatic logging
     * Only log when tags are attached/detached from tasks (handled in Task model)
     */
    protected function shouldLogActivity()
    {
        return false; // Don't log tag creation/updates/deletes
    }
}