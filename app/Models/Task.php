<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use Loggable;

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

    // Relationships
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

    // Logging Customization
    protected function getLoggableFields()
    {
        return ['title', 'description', 'status', 'type', 'priority', 'assignee_id', 'due_date', 'url'];
    }

    protected function customUpdatedDescription($changes)
    {
        // Status change only
        if (isset($changes['status']) && count($changes) === 1) {
            $old = ucfirst(str_replace('_', ' ', $changes['status']['old']));
            $new = ucfirst(str_replace('_', ' ', $changes['status']['new']));
            return "Moved task '{$this->title}' from {$old} to {$new}";
        }

        // Assignee change only
        if (isset($changes['assignee_id']) && count($changes) === 1) {
            $old = $changes['assignee_id']['old'] ? (User::find($changes['assignee_id']['old'])?->name ?? 'Unknown') : 'Unassigned';
            $new = $changes['assignee_id']['new'] ? (User::find($changes['assignee_id']['new'])?->name ?? 'Unknown') : 'Unassigned';

            if (!$changes['assignee_id']['old'])
                return "Assigned task '{$this->title}' to {$new}";
            if (!$changes['assignee_id']['new'])
                return "Unassigned task '{$this->title}'";
            return "Reassigned task '{$this->title}' from {$old} to {$new}";
        }

        // Priority change only
        if (isset($changes['priority']) && count($changes) === 1) {
            $old = $changes['priority']['old'] ? ucfirst($changes['priority']['old']) : 'None';
            $new = $changes['priority']['new'] ? ucfirst($changes['priority']['new']) : 'None';
            return "Changed priority of '{$this->title}' from {$old} to {$new}";
        }

        // Default
        return $this->buildUpdateDescription($changes);
    }

    protected function customCreatedDescription()
    {
        $desc = "Created task '{$this->title}'";
        if ($this->assignee_id && $this->assignee) {
            $desc .= " and assigned to {$this->assignee->name}";
        }
        return $desc;
    }

    protected function customDeletedDescription()
    {
        return "Deleted task '{$this->title}'";
    }

    // Tag Logging
    public function syncTagsWithLog($tagIds)
    {
        $old = $this->tags->pluck('name')->toArray();
        $this->tags()->sync($tagIds);
        $this->load('tags');
        $new = $this->tags->pluck('name')->toArray();

        $added = array_diff($new, $old);
        $removed = array_diff($old, $new);

        if ($added) {
            $this->createLog('tags_attached', "Added tags to '{$this->title}': " . implode(', ', $added));
        }
        if ($removed) {
            $this->createLog('tags_detached', "Removed tags from '{$this->title}': " . implode(', ', $removed));
        }
    }
}