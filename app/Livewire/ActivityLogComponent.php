<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogComponent extends Component
{
    use WithPagination;

    public $boardId;
    public $filterType = '';
    public $filterAction = '';
    public $filterUser = '';
    public $searchQuery = '';
    public $showFilters = false;
    public $perPage = 20;

    protected $queryString = [
        'filterType' => ['except' => ''],
        'filterAction' => ['except' => ''],
        'filterUser' => ['except' => ''],
        'searchQuery' => ['except' => ''],
    ];

    public function mount($boardId)
    {
        $this->boardId = $boardId;
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->reset(['filterType', 'filterAction', 'filterUser', 'searchQuery']);
        $this->resetPage();
    }

    public function loadMore()
    {
        $this->perPage += 20;
    }

    public function render()
    {
        $query = ActivityLog::with(['user', 'board'])
            ->where('board_id', $this->boardId)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($this->filterType) {
            $query->where('model_type', $this->filterType);
        }

        if ($this->filterAction) {
            $query->where('action_type', $this->filterAction);
        }

        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        if ($this->searchQuery) {
            $query->where('description', 'like', '%' . $this->searchQuery . '%');
        }

        $activities = $query->take($this->perPage)->get();
        $hasMore = ActivityLog::where('board_id', $this->boardId)->count() > $this->perPage;

        // Get unique values for filters
        $modelTypes = ActivityLog::where('board_id', $this->boardId)
            ->distinct()
            ->pluck('model_type');

        $actionTypes = ActivityLog::where('board_id', $this->boardId)
            ->distinct()
            ->pluck('action_type');

        $users = ActivityLog::where('board_id', $this->boardId)
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique('id')
            ->filter();

        return view('livewire.activity-log-component', [
            'activities' => $activities,
            'hasMore' => $hasMore,
            'modelTypes' => $modelTypes,
            'actionTypes' => $actionTypes,
            'users' => $users,
        ]);
    }

    public function getActivityIcon($actionType)
    {
        return match($actionType) {
            'created' => '➕',
            'updated' => '✏️',
            'deleted' => '🗑️',
            'status_changed' => '🔄',
            'assigned' => '👤',
            'commented' => '💬',
            'completed' => '✅',
            default => '📝',
        };
    }

    public function getActivityColor($actionType)
    {
        return match($actionType) {
            'created' => '#3fb950',
            'updated' => '#58a6ff',
            'deleted' => '#ef4444',
            'status_changed' => '#a371f7',
            'assigned' => '#fbbf24',
            'commented' => '#8b949e',
            'completed' => '#3fb950',
            default => '#c9d1d9',
        };
    }
}