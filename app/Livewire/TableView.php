<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Task;

class TableView extends Component
{
    public $boardId;
    public $tasks = [];
    
    // Filter properties
    public $statusFilter = '';
    public $priorityFilter = '';
    public $assigneeFilter = '';
    public $searchFilter = '';

    public function mount($boardId)
    {
        $this->boardId = $boardId;
        $this->loadTasks();
    }

    public function loadTasks()
    {
        try {
            $board = Board::with(['tasks' => function($query) {
                $query->where('user_id', auth()->id())
                      ->with(['assignee', 'tags'])
                      ->orderBy('order');
            }])->find($this->boardId);

            if ($board && $board->tasks) {
                $this->tasks = $this->applyFilters($board->tasks);
            } else {
                $this->tasks = collect([]);
            }

            \Log::info('TableView loaded - Board: ' . $this->boardId . ', Tasks: ' . count($this->tasks));
        } catch (\Exception $e) {
            \Log::error('TableView Error: ' . $e->getMessage());
            $this->tasks = collect([]);
        }
    }

    public function applyFilters($tasks)
    {
        $filtered = $tasks;

        // Apply status filter
        if ($this->statusFilter) {
            $filtered = $filtered->filter(function($task) {
                return $task->status === $this->statusFilter;
            });
        }

        // Apply priority filter
        if ($this->priorityFilter) {
            $filtered = $filtered->filter(function($task) {
                return strtolower($task->priority ?? '') === strtolower($this->priorityFilter);
            });
        }

        // Apply assignee filter
        if ($this->assigneeFilter) {
            $filtered = $filtered->filter(function($task) {
                return $task->assignee && $task->assignee->name === $this->assigneeFilter;
            });
        }

        // Apply search filter
        if ($this->searchFilter) {
            $filtered = $filtered->filter(function($task) {
                return stripos($task->title, $this->searchFilter) !== false;
            });
        }

        return $filtered;
    }

    public function updated($property)
    {
        // Reload tasks whenever any filter is updated
        if (in_array($property, ['statusFilter', 'priorityFilter', 'assigneeFilter', 'searchFilter'])) {
            $this->loadTasks();
        }
    }

    public function clearFilters()
    {
        $this->statusFilter = '';
        $this->priorityFilter = '';
        $this->assigneeFilter = '';
        $this->searchFilter = '';
        $this->loadTasks();
    }

    public function openTaskModal($taskId = null)
    {
        if ($taskId) {
            $this->dispatch('openTaskModal', taskId: $taskId);
        } else {
            $this->dispatch('openTaskModal');
        }
    }

    public function render()
    {
        return view('livewire.table-view');
    }
}