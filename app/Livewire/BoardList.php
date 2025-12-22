<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\BudgetCategory;
use Illuminate\Support\Facades\Auth;

/**
 * Displays the user's board list with filtering, search, rename, and delete functionality.
 * Board cards show task/category counts and member avatars.
 */
class BoardList extends Component
{
    public $filterType = 'all';
    public $searchQuery = '';

    public $deleteModalOpen = false;
    public $deleteBoardId;
    public $deleteBoardTitle;

    public $renameModalOpen = false;
    public $renameBoardId;
    public $renameBoardTitle;
    public $newBoardTitle = '';

    protected $listeners = ['board-added' => '$refresh'];

    public function setFilter($type)
    {
        $this->filterType = $type;
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
    }

    public function confirmDelete($id)
    {
        $board = Board::findOrFail($id);

        $userMember = $board->members()->where('user_id', auth()->id())->first();
        $userRole = $userMember ? $userMember->pivot->role : 'member';

        if ($userRole !== 'owner') {
            session()->flash('error', 'Only board owners can delete boards.');
            return;
        }

        $this->deleteBoardId = $id;
        $this->deleteBoardTitle = $board->title;
        $this->deleteModalOpen = true;
    }

    public function closeDeleteModal()
    {
        $this->reset(['deleteModalOpen', 'deleteBoardId', 'deleteBoardTitle']);
    }

    public function deleteBoard()
    {
        $board = Board::findOrFail($this->deleteBoardId);

        $userMember = $board->members()->where('user_id', auth()->id())->first();
        $userRole = $userMember ? $userMember->pivot->role : 'member';

        if ($userRole !== 'owner') {
            session()->flash('error', 'You are not authorized to delete this board.');
            $this->closeDeleteModal();
            return;
        }

        $board->delete();

        $this->closeDeleteModal();
        session()->flash('success', 'Board deleted successfully.');

        // Refresh the component
        $this->dispatch('board-deleted');
    }

    public function openRenameModal($id)
    {
        $board = Board::findOrFail($id);

        $userMember = $board->members()->where('user_id', auth()->id())->first();
        $userRole = $userMember ? $userMember->pivot->role : 'member';

        if (!in_array($userRole, ['owner', 'admin'])) {
            session()->flash('error', 'Only board owners and admins can rename boards.');
            return;
        }

        $this->renameBoardId = $id;
        $this->renameBoardTitle = $board->title;
        $this->newBoardTitle = $board->title;
        $this->renameModalOpen = true;
    }

    public function closeRenameModal()
    {
        $this->reset(['renameModalOpen', 'renameBoardId', 'renameBoardTitle', 'newBoardTitle']);
    }

    public function renameBoard()
    {
        $this->validate([
            'newBoardTitle' => ['required', 'string', 'min:1', 'max:100']
        ]);

        $board = Board::findOrFail($this->renameBoardId);

        $userMember = $board->members()->where('user_id', auth()->id())->first();
        $userRole = $userMember ? $userMember->pivot->role : 'member';

        if (!in_array($userRole, ['owner', 'admin'])) {
            session()->flash('error', 'You are not authorized to rename this board.');
            $this->closeRenameModal();
            return;
        }

        if ($board->title === $this->newBoardTitle) {
            $this->closeRenameModal();
            return;
        }

        $oldTitle = $board->title;
        $board->title = $this->newBoardTitle;
        $board->save();

        $this->closeRenameModal();
        session()->flash('success', 'Board renamed successfully.');

        $this->dispatch('board-renamed');
    }

    public function render()
    {
        $user = Auth::user();

        $query = Board::whereHas('members', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['members'])
            ->withCount('tasks')
            ->orderBy('updated_at', 'desc');

        if ($this->filterType !== 'all') {
            $query->where('list_type', $this->filterType);
        }

        if (!empty($this->searchQuery)) {
            $query->where('title', 'like', '%' . $this->searchQuery . '%');
        }

        $boards = $query->get();

        foreach ($boards as $board) {
            if ($board->list_type === 'Business') {
                $board->budget_categories_count = BudgetCategory::whereHas('budget', function ($q) use ($board) {
                    $q->where('board_id', $board->id);
                })->count();
            }
        }

        return view('livewire.board-list', [
            'boards' => $boards
        ]);
    }
}
