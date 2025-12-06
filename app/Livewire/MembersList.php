<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;

class MembersList extends Component
{
    public $boardId;
    public $board;
    public $members = [];
    public $currentUserRole;

    // Filters
    public $roleFilter = '';
    public $searchFilter = '';
    public $showFilters = false;

    // Add member form
    public $showAddModal = false;
    public $email = '';
    public $role = 'member';

    // Delete confirmation
    public $showDeleteConfirm = false;
    public $deletingMemberId = null;

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
        $this->board = Board::with([
            'members' => function ($q) {
                $q->withPivot('role', 'created_at');
            }
        ])->findOrFail($this->boardId);

        // Set current user role
        $this->setCurrentUserRole();

        // Apply filters to load members
        $this->applyFilters();
    }

    protected function setCurrentUserRole()
    {
        // Check if user is the board owner
        if ($this->board->user_id == auth()->id()) {
            // Ensure owner pivot exists
            $existingPivot = $this->board->members()->where('user_id', auth()->id())->first();

            if (!$existingPivot) {
                $this->board->members()->attach(auth()->id(), [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($existingPivot->pivot->role !== 'owner') {
                // Update to owner if not already
                $this->board->members()->updateExistingPivot(auth()->id(), [
                    'role' => 'owner',
                    'updated_at' => now(),
                ]);
            }

            $this->currentUserRole = 'owner';
            return;
        }

        // Check if user is a member
        $currentMember = $this->board->members()
            ->where('user_id', auth()->id())
            ->first();

        if ($currentMember && isset($currentMember->pivot->role)) {
            $this->currentUserRole = $currentMember->pivot->role;
        } else {
            // User is not a member and not the owner
            abort(403, 'You do not have access to this board.');
        }
    }

    public function applyFilters()
    {
        // Always load fresh with pivot
        $query = $this->board->members()->withPivot('role', 'created_at');

        if ($this->roleFilter) {
            $query->wherePivot('role', $this->roleFilter);
        }

        if ($this->searchFilter) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchFilter}%")
                    ->orWhere('email', 'like', "%{$this->searchFilter}%");
            });
        }

        $this->members = $query->get();
    }

    public function updated($property)
    {
        if (in_array($property, ['roleFilter', 'searchFilter'])) {
            $this->applyFilters();
        }
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->roleFilter = '';
        $this->searchFilter = '';
        $this->applyFilters();
    }

    public function openAddModal()
    {
        // Check permission before opening modal
        if (!$this->canManageMembers()) {
            session()->flash('error', 'You do not have permission to add members.');
            return;
        }

        Gate::authorize('addMember', $this->board);
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->reset(['email', 'role']);
        $this->resetErrorBag();
    }

    public function addMember()
    {
        Gate::authorize('addMember', $this->board);

        $this->validate();

        try {
            $user = User::where('email', $this->email)->first();

            if (!$user) {
                $this->addError('email', 'User not found.');
                return;
            }

            if ($this->board->members()->where('user_id', $user->id)->exists()) {
                $this->addError('email', 'This user is already a member of the board.');
                return;
            }

            $this->board->members()->attach($user->id, [
                'role' => $this->role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ActivityLog::log(
                $this->boardId,
                'Board',
                $this->boardId,
                'member_added',
                auth()->user()->name . ' added ' . $user->name . ' as ' . $this->role
            );

            $this->closeAddModal();
            $this->loadBoard();
            session()->flash('success', 'Member added successfully!');

        } catch (\Exception $e) {
            \Log::error('Error adding member: ' . $e->getMessage());
            session()->flash('error', 'Failed to add member');
        }
    }

    public function askDelete($userId)
    {
        if (!$this->canManageMembers()) {
            session()->flash('error', 'You do not have permission to remove members.');
            return;
        }

        Gate::authorize('removeMember', $this->board);
        $this->deletingMemberId = $userId;
        $this->showDeleteConfirm = true;
    }

    public function removeMember()
    {
        Gate::authorize('removeMember', $this->board);

        try {
            $member = $this->board->members()->where('user_id', $this->deletingMemberId)->first();

            if (!$member) {
                session()->flash('error', 'Member not found');
                $this->cancelDelete();
                return;
            }

            // Prevent removing the last owner
            if ($member->pivot->role === 'owner') {
                $ownerCount = $this->board->members()->wherePivot('role', 'owner')->count();
                if ($ownerCount <= 1) {
                    session()->flash('error', 'Cannot remove the last owner');
                    $this->cancelDelete();
                    return;
                }
            }

            // Prevent users from removing themselves if they're the owner
            if ($this->deletingMemberId === auth()->id() && $member->pivot->role === 'owner') {
                session()->flash('error', 'Transfer ownership before leaving');
                $this->cancelDelete();
                return;
            }

            ActivityLog::log(
                $this->boardId,
                'Board',
                $this->boardId,
                'member_removed',
                auth()->user()->name . ' removed ' . $member->name . ' from the board'
            );

            $this->board->members()->detach($this->deletingMemberId);
            $this->cancelDelete();
            $this->loadBoard();
            session()->flash('success', 'Member removed successfully!');

        } catch (\Exception $e) {
            \Log::error('Error removing member: ' . $e->getMessage());
            session()->flash('error', 'Failed to remove member');
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirm = false;
        $this->deletingMemberId = null;
    }

    public function updateRole($userId, $newRole)
    {
        if (!$this->canManageMembers()) {
            session()->flash('error', 'You do not have permission to update roles.');
            $this->loadBoard(); // Reload to reset the dropdown
            return;
        }

        try {
            // Get the member with pivot data
            $member = $this->board->members()
                ->withPivot('role')
                ->where('user_id', $userId)
                ->first();

            if (!$member) {
                session()->flash('error', 'Member not found');
                $this->loadBoard();
                return;
            }

            $oldRole = $member->pivot->role;

            // Update the role in the database
            $this->board->members()->updateExistingPivot($userId, [
                'role' => $newRole,
                'updated_at' => now(),
            ]);

            // Log the activity
            ActivityLog::log(
                $this->boardId,
                'Board',
                $this->boardId,
                'role_changed',
                auth()->user()->name . ' changed ' . $member->name . "'s role from " .
                $oldRole . ' to ' . $newRole
            );

            // Reload the board to reflect changes
            $this->loadBoard();

            session()->flash('success', 'Role updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Error updating role: ' . $e->getMessage());
            session()->flash('error', 'Failed to update role');
            $this->loadBoard(); // Reload on error to reset state
        }
    }

    public function canManageMembers()
    {
        // Ensure currentUserRole is set
        if (!isset($this->currentUserRole) || $this->currentUserRole === null) {
            $this->setCurrentUserRole();
        }

        return in_array($this->currentUserRole, ['owner', 'admin']);
    }

    public function render()
    {
        return view('livewire.members-list');
    }
}