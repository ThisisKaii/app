<?php

// ============================================
// Board.php
// ============================================

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use Loggable;
    
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

    // ============================================
    // Activity Logging Customization
    // ============================================

    /**
     * Override to use board's own ID (boards don't have board_id)
     */
    protected function getBoardId()
    {
        return $this->id;
    }

    protected function getLoggableAttributes()
    {
        return ['title', 'list_type'];
    }

    protected function getAttributeLabel($attribute)
    {
        return $attribute === 'list_type' ? 'List type' : parent::getAttributeLabel($attribute);
    }

    protected function getCreatedDescription()
    {
        return "Created board '{$this->title}' ({$this->list_type})";
    }

    protected function getUpdatedDescription($changes)
    {
        if (isset($changes['title']) && count($changes) === 1) {
            return "Renamed board from '{$changes['title']['old']}' to '{$changes['title']['new']}'";
        }
        return parent::getUpdatedDescription($changes);
    }

    protected function getDeletedDescription()
    {
        return "Deleted board '{$this->title}'";
    }

    // Custom logging methods for member management
    public function logMemberAdded($memberName, $role)
    {
        return $this->logActivity('member_added', "Added {$memberName} as {$role}");
    }

    public function logMemberRemoved($memberName)
    {
        return $this->logActivity('member_removed', "Removed {$memberName} from board");
    }

    public function logRoleChanged($memberName, $oldRole, $newRole)
    {
        return $this->logActivity('role_changed', "Changed {$memberName}'s role from {$oldRole} to {$newRole}");
    }
}