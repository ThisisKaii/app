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

        // Get only board members with their tasks for THIS board
        $this->teamMembers = $this->board->members()
            ->with([
                'assignedTasks' => function ($query) {
                    $query->where('board_id', $this->boardId)
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->get()
            ->map(function ($member) {
                // Map assignedTasks to tasks for compatibility with the view
                $member->tasks = $member->assignedTasks;
                return $member;
            });

        // Get unassigned tasks only for THIS board
        $this->unassignedTasks = Task::where('board_id', $this->boardId)
            ->whereNull('assignee_id')
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
        return view('livewire.teams');
    }
}