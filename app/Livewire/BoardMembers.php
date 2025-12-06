<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class BoardMembers extends Component
{
    public $boardId;
    public $board;
    public $showModal = false;
    public $email = '';
    public $role = 'member';
    public $members = [];
    public $currentUserRole;

    protected $rules = [
        'email' => 'required|email|exists:users,email',
        'role' => 'required|in:member,admin',
    ];

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->loadBoard();
    }

    public function loadBoard()
    {
        $this->board = Board::with(['members' => function($query) {
            $query->orderBy('board_members.role', 'desc');
        }])->findOrFail($this->boardId);
        
        $this->members = $this->board->members;
        
        // Get current user's role
        $currentMember = $this->board->members()->where('user_id', auth()->id())->first();
        $this->currentUserRole = $currentMember ? $currentMember->pivot->role : null;
        
        // Check authorization
        Gate::authorize('view', $this->board);
    }

    public function openModal()
    {
        Gate::authorize('addMember', $this->board);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['email', 'role']);
        $this->resetErrorBag();
    }

    public function addMember()
    {
        Gate::authorize('addMember', $this->board);
        
        $this->validate();

        try {
            $user = User::where('email', $this->email)->first();

            // Check if user is already a member
            if ($this->board->members()->where('user_id', $user->id)->exists()) {
                $this->addError('email', 'This user is already a member of the board.');
                return;
            }

            // Add member
            $this->board->members()->attach($user->id, [
                'role' => $this->role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->closeModal();
            $this->loadBoard();
            $this->dispatch('showToast', message: 'Member added successfully!', type: 'success');
            
        } catch (\Exception $e) {
            \Log::error('Error adding member: ' . $e->getMessage());
            $this->dispatch('showToast', message: 'Failed to add member', type: 'error');
        }
    }

    public function removeMember($userId)
    {
        Gate::authorize('removeMember', $this->board);

        try {
            $member = $this->board->members()->where('user_id', $userId)->first();
            
            // Prevent removing the last owner
            if ($member->pivot->role === 'owner') {
                $ownerCount = $this->board->members()->wherePivot('role', 'owner')->count();
                if ($ownerCount <= 1) {
                    $this->dispatch('showToast', message: 'Cannot remove the last owner', type: 'error');
                    return;
                }
            }

            // Prevent users from removing themselves if they're the owner
            if ($userId === auth()->id() && $member->pivot->role === 'owner') {
                $this->dispatch('showToast', message: 'Transfer ownership before leaving', type: 'error');
                return;
            }

            $this->board->members()->detach($userId);
            $this->loadBoard();
            $this->dispatch('showToast', message: 'Member removed successfully!', type: 'success');
            
        } catch (\Exception $e) {
            \Log::error('Error removing member: ' . $e->getMessage());
            $this->dispatch('showToast', message: 'Failed to remove member', type: 'error');
        }
    }

    public function updateRole($userId, $newRole)
    {
        Gate::authorize('update', $this->board);

        try {
            $this->board->members()->updateExistingPivot($userId, [
                'role' => $newRole,
                'updated_at' => now(),
            ]);

            $this->loadBoard();
            $this->dispatch('showToast', message: 'Role updated successfully!', type: 'success');
            
        } catch (\Exception $e) {
            \Log::error('Error updating role: ' . $e->getMessage());
            $this->dispatch('showToast', message: 'Failed to update role', type: 'error');
        }
    }

    public function canManageMembers()
    {
        return in_array($this->currentUserRole, ['owner', 'admin']);
    }

    public function render()
    {
        return view('livewire.board-members');
    }
}