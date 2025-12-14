<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class TaskGroup extends Model
{
    use Loggable;

    protected $fillable = [
        'board_id',
        'title',
        'status',
        'order'
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'group_id')->orderBy('order');
    }

    // Logging Customization
    protected function getLoggableFields()
    {
        return ['title', 'status'];
    }

    protected function customCreatedDescription()
    {
        return "Created task group '{$this->title}'";
    }

    protected function customUpdatedDescription($changes)
    {
        if (isset($changes['status']) && count($changes) === 1) {
            $old = ucfirst(str_replace('_', ' ', $changes['status']['old']));
            $new = ucfirst(str_replace('_', ' ', $changes['status']['new']));
            return "Moved group '{$this->title}' from {$old} to {$new}";
        }

        if (isset($changes['title']) && count($changes) === 1) {
            return "Renamed group from '{$changes['title']['old']}' to '{$changes['title']['new']}'";
        }

        return $this->buildUpdateDescription($changes);
    }

    protected function customDeletedDescription()
    {
        return "Deleted task group '{$this->title}'";
    }
}
