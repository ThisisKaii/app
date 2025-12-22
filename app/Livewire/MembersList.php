<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;

/**
 * Board member management component with role-based permissions.
 * Allows Owner/Admin to invite, remove, and change roles of members.
 */
class MembersList extends Component
{
    public $boardId;
    public $board;
    public $currentUserRole;

    public $roleFilter = '';
    public $searchFilter = '';
    public $showFilters = false;

    public $showAddModal = false;
    public $email = '';
    public $role = 'member';

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

        $this->setCurrentUserRole();
    }

    protected function setCurrentUserRole()
    {
        if ($this->board->user_id == auth()->id()) {
            $existingPivot = $this->board->members()->where('user_id', auth()->id())->first();

            if (!$existingPivot) {
                $this->board->members()->attach(auth()->id(), [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($existingPivot->pivot->role !== 'owner') {
                $this->board->members()->updateExistingPivot(auth()->id(), [
                    'role' => 'owner',
                    'updated_at' => now(),
                ]);
            }

            $this->currentUserRole = 'owner';
            return;
        }

        $currentMember = $this->board->members()
            ->where('user_id', auth()->id())
            ->first();

        if ($currentMember && isset($currentMember->pivot->role)) {
            $this->currentUserRole = $currentMember->pivot->role;
        } else {
            abort(403, 'You do not have access to this board.');
        }
    }

    public function updated($property)
    {
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->roleFilter = '';
        $this->searchFilter = '';
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

        $members = $query->get();

        return view('livewire.members-list', [
            'members' => $members,
        ]);
    }
}