<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use Loggable;

    protected $fillable = ['user_id', 'title', 'list_type'];

    protected $casts = [
        'list_type' => 'string',
    ];

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
    protected function getLogBoardId()
    {
        return $this->id;
    }

    protected function getLoggableFields()
    {
        return ['title', 'list_type'];
    }

    protected function customCreatedDescription()
    {
        $type = $this->list_type === 'business' ? 'Business' : 'Normal';
        return "Created {$type} board '{$this->title}'";
    }

    protected function customUpdatedDescription($changes)
    {
        if (isset($changes['title']) && count($changes) === 1) {
            return "Renamed board from '{$changes['title']['old']}' to '{$changes['title']['new']}'";
        }

        if (isset($changes['list_type']) && count($changes) === 1) {
            $oldType = $changes['list_type']['old'] === 'business' ? 'Business' : 'Normal';
            $newType = $changes['list_type']['new'] === 'business' ? 'Business' : 'Normal';
            return "Changed board type from {$oldType} to {$newType}";
        }

        return $this->buildUpdateDescription($changes);
    }

    protected function customDeletedDescription()
    {
        $type = $this->list_type === 'business' ? 'Business' : 'Normal';
        return "Deleted {$type} board '{$this->title}'";
    }

    // Custom logging methods for member management
    public function logMemberAdded($memberName, $role)
    {
        return $this->createLog('member_added', "Added {$memberName} as {$role}");
    }

    public function logMemberRemoved($memberName)
    {
        return $this->createLog('member_removed', "Removed {$memberName} from board");
    }

    public function logRoleChanged($memberName, $oldRole, $newRole)
    {
        return $this->createLog('role_changed', "Changed {$memberName}'s role from {$oldRole} to {$newRole}");
    }

    // Helper method to get formatted board type
    public function getFormattedTypeAttribute()
    {
        return $this->list_type === 'business' ? 'Business' : 'Normal';
    }
}