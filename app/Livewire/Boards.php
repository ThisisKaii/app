<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;
use App\Models\Task;

class Boards extends Component
{
    public $board;
    public $boardId;
    public $task;

    public function mount($boardId)
    {
        $this->board = Board::findOrFail($boardId);
        \Log::info('Boards component mounting', [
            'board_id' => $boardId,
            'list_type' => $this->board->list_type,
        ]);
        // This component should only be used for normal boards (capitalized)
        // Business boards use BudgetBoard component instead
        if ($this->board->list_type === 'Business') {
            abort(403, 'This component is for normal boards only. Business boards use the budget interface.');
        }

        // Load tasks for normal boards
        $this->task = Task::where('board_id', $boardId)
            ->with(['assignee', 'tags'])
            ->orderBy('order')
            ->get();
    }

    public function render()
    {
        return view('livewire.board');
    }
}