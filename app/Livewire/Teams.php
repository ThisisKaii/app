<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Task;
use App\Models\Board;
use Livewire\Component;

class Teams extends Component
{
    public $boardId;
    public $board;
    public $teamMembers;
    public $unassignedTasks;
    public $expandedMembers = [];

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->loadTeamData();
    }

    public function loadTeamData()
    {
        // Load the board
        $this->board = Board::findOrFail($this->boardId);

        // Get board members
        $members = $this->board->members()->get();
        
        // For each member, get their tasks for THIS board only
        $this->teamMembers = $members->map(function ($member) {
            // Query tasks assigned to this user for THIS board specifically
            $member->tasks = Task::where('board_id', $this->boardId)
                ->where(function($query) use ($member) {
                    // Check both old assignee_id and new task_user pivot
                    $query->where('assignee_id', $member->id)
                          ->orWhereHas('users', function($q) use ($member) {
                              $q->where('user_id', $member->id);
                          });
                })
                ->orderBy('created_at', 'desc')
                ->get();
            return $member;
        });

        // Get unassigned tasks only for THIS board
        $this->unassignedTasks = Task::where('board_id', $this->boardId)
            ->whereNull('assignee_id')
            ->whereDoesntHave('users') // Also check new relationship
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function showMore($memberId)
    {
        // Toggle the expanded state for this member
        if (in_array($memberId, $this->expandedMembers)) {
            $this->expandedMembers = array_diff($this->expandedMembers, [$memberId]);
        } else {
            $this->expandedMembers[] = $memberId;
        }
    }

    public function showMoreUnassigned()
    {
        // Toggle the expanded state for unassigned
        if (in_array('unassigned', $this->expandedMembers)) {
            $this->expandedMembers = array_diff($this->expandedMembers, ['unassigned']);
        } else {
            $this->expandedMembers[] = 'unassigned';
        }
    }

    public function isExpanded($memberId)
    {
        return in_array($memberId, $this->expandedMembers);
    }

    public function render()
    {
        // Reload data on every render (including wire:poll refreshes)
        $this->loadTeamData();
        
        return view('livewire.teams');
    }
}