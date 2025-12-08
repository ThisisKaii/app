<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Board;

class AddBoard extends Component
{
    public $title = '';
    public $list_type = '';
    public $board;
    public $isOpen = false;

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['title', 'list_type']);
        $this->resetValidation();
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:100',
            'list_type' => 'required|string|in:Normal,Business',
        ]);
        
        // Debug log
        \Log::info('Creating board with list_type: ' . $validated['list_type']);

        try {
            // Create the board
            $board = Board::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'list_type' => $validated['list_type'], // Will now be 'Normal' or 'Business'
            ]);

            // Add creator as owner in board_members pivot table
            $board->members()->attach(auth()->id(), [
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // If it's a business board, create the initial budget
            if ($board->list_type === 'Business') { // Changed to capitalized
                \App\Models\Budgets::create([
                    'board_id' => $board->id,
                    'total_budget' => 0,
                ]);
            }

            $this->closeModal();

            $boardType = $board->list_type === 'Business' ? 'Business' : 'Normal';
            session()->flash('success', "{$boardType} board created successfully!");

            // Dispatch event to refresh board list
            $this->dispatch('board-added');

            // Redirect to the board
            return redirect()->route('boards.show', ['board' => $board->id]);

        } catch (\Exception $e) {
            \Log::error('Board creation error: ' . $e->getMessage());
            session()->flash('error', 'Failed to create board. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.add-board');
    }
}